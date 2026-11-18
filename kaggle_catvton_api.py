import os, sys, io, base64, time, threading, subprocess
from typing import Optional
from PIL import Image
import torch

print("🐈 Starting CatVTON Virtual Try-On API...")

# Clean start: remove old nested clones
subprocess.run(["rm", "-rf", "CatVTON"], check=False)

# Clone CatVTON fresh
subprocess.run(["git", "clone", "https://github.com/Zheng-Chong/CatVTON.git"], check=True)
os.chdir("CatVTON")
sys.path.insert(0, os.getcwd())

print("✅ Repository ready\n")

# Install only compatible versions (use what works in Kaggle)
print("📦 Installing dependencies...")
subprocess.run([
    sys.executable, "-m", "pip", "install", "-q",
    "--upgrade", "pip"
], check=False)

# Install in order to avoid conflicts
pkgs_core = ["einops", "omegaconf", "scipy", "opencv-python", "av"]
pkgs_hf = ["diffusers", "transformers", "accelerate", "huggingface_hub"]
pkgs_api = ["fastapi", "uvicorn", "pyngrok", "cloudpickle", "fvcore"]

for pkg in pkgs_core:
    subprocess.run([sys.executable, "-m", "pip", "install", "-q", pkg], check=False)

for pkg in pkgs_hf:
    subprocess.run([sys.executable, "-m", "pip", "install", "-q", pkg], check=False)

for pkg in pkgs_api:
    subprocess.run([sys.executable, "-m", "pip", "install", "-q", pkg], check=False)

subprocess.run([sys.executable, "-m", "pip", "install", "-q", "onnxruntime-gpu"], check=False)

print("✅ Dependencies installed\n")

# Check GPU
device = "cuda" if torch.cuda.is_available() else "cpu"
assert device == "cuda", "❌ No GPU detected!"
print(f"✅ GPU: {torch.cuda.get_device_name(0)}\n")

# Import after installs
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import uvicorn
from pyngrok import ngrok
from huggingface_hub import snapshot_download
from model.pipeline import CatVTONPipeline
from model.cloth_masker import AutoMasker
from utils import resize_and_crop, resize_and_padding

# Load models
print("📥 Downloading CatVTON models...")
model_path = snapshot_download("zhengchong/CatVTON", cache_dir="./checkpoints")
print("🔄 Initializing pipeline...")

pipeline = CatVTONPipeline(
    base_ckpt="booksforcharlie/stable-diffusion-inpainting",
    attn_ckpt=model_path,
    attn_ckpt_version="mix",
    weight_dtype=torch.float16,
    use_tf32=True,
    device=device
)

print("🔄 Initializing AutoMasker...")
automasker = AutoMasker(
    densepose_ckpt=os.path.join(model_path, "DensePose"),
    schp_ckpt=os.path.join(model_path, "SCHP"),
    device=device
)

print("✅ Models loaded!\n")

# FastAPI app
app = FastAPI(title="CatVTON Virtual Try-On API", version="1.0.0")

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
    return {
        "status": "online",
        "service": "CatVTON Virtual Try-On API",
        "model": "CatVTON (Official)",
        "device": device.upper(),
        "gpu": torch.cuda.is_available()
    }

@app.get("/health")
def health():
    return {
        "status": "healthy",
        "model_loaded": True,
        "gpu_available": torch.cuda.is_available(),
        "device": device.upper()
    }

@app.post("/api/tryon", response_model=TryOnResponse)
def tryon(request: TryOnRequest):
    start_time = time.time()
    try:
        print(f"\n{'='*60}")
        print(f"📸 Virtual Try-On Request - {request.cloth_type}")

        # Decode images
        person_b64 = request.person_image_base64.split(",")[-1] if "," in request.person_image_base64 else request.person_image_base64
        cloth_b64 = request.cloth_image_base64.split(",")[-1] if "," in request.cloth_image_base64 else request.cloth_image_base64

        person_image = Image.open(io.BytesIO(base64.b64decode(person_b64))).convert("RGB")
        cloth_image = Image.open(io.BytesIO(base64.b64decode(cloth_b64))).convert("RGB")

        print("🎨 Resizing...")
        person_image = resize_and_crop(person_image, (768, 1024))
        cloth_image = resize_and_padding(cloth_image, (768, 1024))

        print("🎭 Generating mask...")
        mask = automasker(person_image, request.cloth_type)["mask"]

        print("🤖 Running CatVTON...")
        with torch.inference_mode():
            result_image = pipeline(
                image=person_image,
                condition_image=cloth_image,
                mask=mask,
                num_inference_steps=30,
                guidance_scale=2.5,
                generator=torch.Generator(device=device).manual_seed(42)
            )

        buffer = io.BytesIO()
        result_image.save(buffer, format="PNG", quality=95)
        buffer.seek(0)
        image_base64 = base64.b64encode(buffer.getvalue()).decode("utf-8")

        elapsed = time.time() - start_time
        print(f"✅ Complete! ({elapsed:.2f}s)")
        print(f"{'='*60}\n")

        return TryOnResponse(
            status="success",
            image_base64=image_base64,
            processing_time=round(elapsed, 2)
        )

    except Exception as e:
        import traceback
        print(f"❌ Error: {e}")
        traceback.print_exc()
        elapsed = time.time() - start_time
        return TryOnResponse(
            status="error",
            error=str(e),
            processing_time=round(elapsed, 2)
        )

# Ngrok setup
print("="*70)
print("🌍 Setting up Ngrok tunnel...")
print("="*70 + "\n")

try:
    from kaggle_secrets import UserSecretsClient
    ngrok_token = UserSecretsClient().get_secret("NGROK_AUTH_TOKEN")
    print("✅ Ngrok token loaded")
except Exception as e:
    print(f"❌ Error: {e}")
    sys.exit(1)

try:
    ngrok.set_auth_token(ngrok_token)
except:
    pass  # Already set

print("🔗 Connecting...")
public_url = ngrok.connect(8000).public_url

print("\n" + "="*70)
print("✅ SERVER IS RUNNING!")
print("="*70)
print(f"\n🌐 API URL: {public_url}")
print(f"📖 Docs: {public_url}/docs")
print(f"\n📋 Add to Laravel .env:")
print(f"   TRYON_API_URL={public_url}")
print(f"\n{'='*70}\n")

threading.Thread(
    target=uvicorn.run,
    args=(app,),
    kwargs={"host": "0.0.0.0", "port": 8000, "log_level": "info"},
    daemon=True
).start()

time.sleep(2)
print("💡 Server live! Keep notebook active.\n")

try:
    while True:
        time.sleep(60)
except KeyboardInterrupt:
    print("\n🛑 Shutting down...")
