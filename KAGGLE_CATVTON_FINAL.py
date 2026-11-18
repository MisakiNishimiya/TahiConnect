import os, sys, io, base64, time, threading, subprocess, shutil

from typing import Optional

from PIL import Image

import torch

print("🐈 CatVTON Virtual Try-On API\n")

# 1. Clean start & clone

os.chdir("/kaggle/working")

if os.path.exists("CatVTON"):

    shutil.rmtree("CatVTON")

subprocess.run(["git", "clone", "https://github.com/Zheng-Chong/CatVTON.git"], check=True, capture_output=True)

os.chdir("CatVTON")

sys.path.insert(0, os.getcwd())

# 2. Install ONLY exact compatible packages (DO NOT upgrade peft/accelerate)

deps = [

    "einops", "omegaconf", "scipy", "opencv-python", "av",

    "diffusers==0.27.2", "transformers==4.39.3", "accelerate==0.28.0", "peft==0.10.0",

    "huggingface_hub==0.16.4", "fastapi", "uvicorn", "pyngrok", "cloudpickle", "fvcore"

]

subprocess.run([sys.executable, "-m", "pip", "install", "-q"] + deps, check=False)

from fastapi import FastAPI

from pydantic import BaseModel

import uvicorn

from pyngrok import ngrok, conf

from huggingface_hub import snapshot_download

from model.pipeline import CatVTONPipeline

from model.cloth_masker import AutoMasker

from utils import resize_and_crop, resize_and_padding

device = "cuda" if torch.cuda.is_available() else "cpu"

assert device == "cuda", "❌ Enable GPU T4 in Kaggle Settings!"

print(f"✅ GPU: {torch.cuda.get_device_name(0)}\n")

# 3. Load Models

print("📥 Downloading & loading CatVTON...")

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

# 4. FastAPI Server

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

        print("🤖 Running pipeline...")
        with torch.inference_mode():

            result_img = pipeline(

                image=person_img,

                condition_image=cloth_img,

                mask=mask,

                num_inference_steps=30,

                guidance_scale=2.5,

                generator=torch.Generator(device=device).manual_seed(42)

            )

        buf = io.BytesIO()

        result_img.save(buf, format="PNG", quality=95)

        img_b64 = base64.b64encode(buf.getvalue()).decode("utf-8")

        elapsed = round(time.time() - t0, 2)

        print(f"✅ Complete! ({elapsed}s)")
        print(f"{'='*60}\n")

        return TryOnResponse(status="success", image_base64=img_b64, processing_time=elapsed)

    except Exception as e:

        import traceback

        print(f"❌ Error: {e}")
        traceback.print_exc()

        return TryOnResponse(status="error", error=str(e), processing_time=round(time.time() - t0, 2))

# 5. Start Ngrok with Static Domain (no set_auth_token crash)

print("="*70)
print("🌍 Setting up Ngrok tunnel...")
print("="*70 + "\n")

from kaggle_secrets import UserSecretsClient

conf.get_default().auth_token = UserSecretsClient().get_secret("NGROK_AUTH_TOKEN")

url = ngrok.connect(8000, domain="nongeographical-derek-indomitally.ngrok-free.dev").public_url

print(f"✅ SERVER RUNNING!\n")
print(f"🌐 API: {url}")
print(f"📖 Docs: {url}/docs")
print(f"\n📋 Add to Laravel .env:")
print(f"   TRYON_API_URL={url}\n")
print("="*70 + "\n")

threading.Thread(target=uvicorn.run, args=(app,), kwargs={"host": "0.0.0.0", "port": 8000, "log_level": "error"}, daemon=True).start()

time.sleep(2)
print("💡 Server running! Keep notebook active.\n")

while True:

    time.sleep(60)
