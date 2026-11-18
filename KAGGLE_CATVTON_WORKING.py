import os, sys, subprocess, shutil
print("🐈 CatVTON Virtual Try-On API\n")

# === FRESH CLEAN START ===
os.chdir("/kaggle/working")
if os.path.exists("CatVTON"):
    shutil.rmtree("CatVTON")
subprocess.run(["git", "clone", "https://github.com/Zheng-Chong/CatVTON.git"], check=True, capture_output=True)
os.chdir("CatVTON")
sys.path.insert(0, os.getcwd())
print("✅ Repository ready\n")

# === UNINSTALL CONFLICTING PACKAGES ===
print("🔧 Cleaning environment...")
subprocess.run([sys.executable, "-m", "pip", "uninstall", "-q", "-y", 
                "diffusers", "transformers", "accelerate", "huggingface_hub", "peft"], 
               check=False)
print()

# === INSTALL WORKING VERSIONS (from scratch) ===
print("📦 Installing dependencies...")
base = ["einops", "omegaconf", "scipy", "opencv-python", "av"]
subprocess.run([sys.executable, "-m", "pip", "install", "-q"] + base, check=False)

# Install HF ecosystem in correct order (HF first, then others)
print("   - huggingface_hub...")
subprocess.run([sys.executable, "-m", "pip", "install", "-q", "huggingface_hub>=0.25.0"], check=False)

print("   - diffusers...")
subprocess.run([sys.executable, "-m", "pip", "install", "-q", "diffusers>=0.21.0"], check=False)

print("   - transformers & accelerate...")
subprocess.run([sys.executable, "-m", "pip", "install", "-q", "transformers>=4.30.0", "accelerate>=0.20.0"], check=False)

print("   - peft...")
subprocess.run([sys.executable, "-m", "pip", "install", "-q", "peft>=0.4.0"], check=False)

api = ["fastapi", "uvicorn", "pyngrok", "cloudpickle", "fvcore"]
subprocess.run([sys.executable, "-m", "pip", "install", "-q"] + api, check=False)

print("✅ Dependencies installed\n")

# === IMPORT & SETUP ===
import io, base64, time, threading
from typing import Optional
from PIL import Image
import torch
from fastapi import FastAPI
from pydantic import BaseModel
import uvicorn
from pyngrok import ngrok, conf
from huggingface_hub import snapshot_download
from model.pipeline import CatVTONPipeline
from model.cloth_masker import AutoMasker
from utils import resize_and_crop, resize_and_padding

device = "cuda" if torch.cuda.is_available() else "cpu"
assert device == "cuda"
print(f"✅ GPU: {torch.cuda.get_device_name(0)}\n")

# === LOAD MODELS ===
print("📥 Loading CatVTON...")
model_path = snapshot_download("zhengchong/CatVTON", cache_dir="./checkpoints")

pipeline = CatVTONPipeline(
    base_ckpt="booksforcharlie/stable-diffusion-inpainting",
    attn_ckpt=model_path,
    attn_ckpt_version="mix",
    weight_dtype=torch.float16,
    use_tf32=True,
    device=device
)

automasker = AutoMasker(
    densepose_ckpt=os.path.join(model_path, "DensePose"),
    schp_ckpt=os.path.join(model_path, "SCHP"),
    device=device
)
print("✅ Models ready!\n")

# === API ===
app = FastAPI()

class TryOnRequest(BaseModel):
    person_image_base64: str
    cloth_image_base64: str
    cloth_type: str = "upper"

class TryOnResponse(BaseModel):
    status: str
    image_base64: Optional[str] = None
    error: Optional[str] = None
    processing_time: Optional[float] = None

@app.get("/")
def root():
    return {"status": "online", "model": "CatVTON", "device": device}

@app.get("/health")
def health():
    return {"status": "healthy", "gpu": True}

@app.post("/api/tryon", response_model=TryOnResponse)
def tryon(request: TryOnRequest):
    t0 = time.time()
    try:
        print(f"\n{'='*60}")
        print(f"📸 Try-On: {request.cloth_type.upper()}")

        person_b64 = request.person_image_base64.split(",")[-1] if "," in request.person_image_base64 else request.person_image_base64
        cloth_b64 = request.cloth_image_base64.split(",")[-1] if "," in request.cloth_image_base64 else request.cloth_image_base64

        person_img = resize_and_crop(Image.open(io.BytesIO(base64.b64decode(person_b64))).convert("RGB"), (768, 1024))
        cloth_img = resize_and_padding(Image.open(io.BytesIO(base64.b64decode(cloth_b64))).convert("RGB"), (768, 1024))

        print("🎭 Generating mask...")
        mask = automasker(person_img, request.cloth_type)["mask"]

        print("🤖 Running CatVTON...")
        with torch.inference_mode():
            result_images = pipeline(
                image=person_img,
                condition_image=cloth_img,
                mask=mask,
                num_inference_steps=30,
                guidance_scale=2.5,
                generator=torch.Generator(device=device).manual_seed(42)
            )
        
        # Extract the first image from the result
        result_img = result_images.images[0] if hasattr(result_images, 'images') else result_images[0]

        buf = io.BytesIO()
        result_img.save(buf, format="PNG", quality=95)
        img_b64 = base64.b64encode(buf.getvalue()).decode()

        elapsed = round(time.time() - t0, 2)
        print(f"✅ Complete ({elapsed}s)")
        print(f"{'='*60}\n")

        return TryOnResponse(status="success", image_base64=img_b64, processing_time=elapsed)

    except Exception as e:
        import traceback
        print(f"❌ {e}")
        traceback.print_exc()
        return TryOnResponse(status="error", error=str(e), processing_time=round(time.time() - t0, 2))

# === NGROK ===
print("="*70)
print("🌍 Starting Ngrok tunnel...\n")

from kaggle_secrets import UserSecretsClient
conf.get_default().auth_token = UserSecretsClient().get_secret("NGROK_AUTH_TOKEN")

url = ngrok.connect(8000).public_url

print(f"✅ SERVER RUNNING!\n")
print(f"🌐 API: {url}")
print(f"📖 Docs: {url}/docs")
print(f"\n📋 Add to Laravel .env:")
print(f"   TRYON_API_URL={url}\n")
print("="*70 + "\n")

threading.Thread(target=uvicorn.run, args=(app,), kwargs={"host": "0.0.0.0", "port": 8000, "log_level": "error"}, daemon=True).start()

time.sleep(2)
print("💡 Server live! Keep notebook running.\n")

while True:
    time.sleep(60)
