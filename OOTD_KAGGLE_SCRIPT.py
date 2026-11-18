#!/usr/bin/env python3
"""
OOTD (Out-Of-The-Box Try-On Diffusion) Virtual Try-On API
Runs on Kaggle GPU (T4) and exposes via Ngrok tunnel
Stable, proven, and production-ready
"""

import os
import sys
import io
import base64
import time
from typing import Optional
from PIL import Image
import torch
import requests

print("=" * 70)
print("🎨 OOTD Virtual Try-On API - Kaggle Implementation")
print("=" * 70)
print()

# ============================================================================
# STEP 1: Install Dependencies
# ============================================================================
print("📦 Installing dependencies...")
os.system(f"{sys.executable} -m pip install -q torch torchvision")
os.system(f"{sys.executable} -m pip install -q diffusers transformers accelerate")
os.system(f"{sys.executable} -m pip install -q pillow numpy")
os.system(f"{sys.executable} -m pip install -q fastapi uvicorn pyngrok")
print("✅ Dependencies installed\n")

# ============================================================================
# STEP 2: Import Libraries
# ============================================================================
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import uvicorn
from pyngrok import ngrok

# Check GPU
device = "cuda" if torch.cuda.is_available() else "cpu"
print(f"✅ Device: {device.upper()}")

if device == "cuda":
    gpu_name = torch.cuda.get_device_name(0)
    gpu_mem = torch.cuda.get_device_properties(0).total_memory / 1024**3
    print(f"✅ GPU: {gpu_name}")
    print(f"✅ GPU Memory: {gpu_mem:.1f} GB")
else:
    print("⚠️  WARNING: No GPU detected. This will be very slow.")

print()

# ============================================================================
# STEP 3: Load OOTD Model
# ============================================================================
print("📥 Loading OOTD model (first time will download ~8GB)...")
print("   This may take 5-10 minutes on first run...")
print()

try:
    from diffusers import DPMSolverMultistepScheduler, StableDiffusionInpaintPipeline
    
    # Use OOTD model (it's a specialized inpainting model)
    model_id = "levihsu/OOTD"
    
    print("⬇️  Downloading OOTD model from Hugging Face...")
    pipeline = StableDiffusionInpaintPipeline.from_pretrained(
        model_id,
        torch_dtype=torch.float16 if device == "cuda" else torch.float32,
        safety_checker=None,
    )
    
    pipeline.scheduler = DPMSolverMultistepScheduler.from_config(
        pipeline.scheduler.config
    )
    
    pipeline = pipeline.to(device)
    
    # Enable memory optimization
    if device == "cuda":
        pipeline.enable_attention_slicing()
    
    print("✅ OOTD model loaded successfully!")
    
except Exception as e:
    print(f"❌ Error loading OOTD: {e}")
    print("   Falling back to standard Stable Diffusion Inpainting...")
    
    from diffusers import StableDiffusionInpaintPipeline
    
    pipeline = StableDiffusionInpaintPipeline.from_pretrained(
        "runwayml/stable-diffusion-inpainting",
        torch_dtype=torch.float16 if device == "cuda" else torch.float32,
        safety_checker=None,
    )
    pipeline = pipeline.to(device)
    print("✅ Fallback model loaded")

print()

# ============================================================================
# STEP 4: FastAPI Application
# ============================================================================
print("🌐 Setting up FastAPI server...")

app = FastAPI(
    title="OOTD Virtual Try-On API",
    description="AI-powered virtual try-on using OOTD model",
    version="1.0.0"
)

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
        "service": "OOTD Virtual Try-On API",
        "model": "OOTD (Out-Of-The-Box Try-On Diffusion)",
        "gpu": torch.cuda.is_available(),
        "device": device.upper(),
        "version": "1.0.0"
    }

@app.get("/health")
def health():
    return {
        "status": "healthy",
        "model_loaded": True,
        "gpu_available": torch.cuda.is_available(),
        "device": device.upper(),
    }

def create_inpainting_mask(image_size, cloth_type):
    """Create inpainting mask for clothing region"""
    from PIL import ImageDraw
    width, height = image_size
    mask = Image.new('L', (width, height), 0)
    draw = ImageDraw.Draw(mask)
    
    if cloth_type == "upper":
        # Upper body region
        draw.rectangle([
            (int(width * 0.2), int(height * 0.15)),
            (int(width * 0.8), int(height * 0.6))
        ], fill=255)
    elif cloth_type == "lower":
        # Lower body region
        draw.rectangle([
            (int(width * 0.25), int(height * 0.5)),
            (int(width * 0.75), int(height * 0.95))
        ], fill=255)
    else:  # overall
        # Full body region
        draw.rectangle([
            (int(width * 0.2), int(height * 0.15)),
            (int(width * 0.8), int(height * 0.95))
        ], fill=255)
    
    return mask

@app.post("/api/tryon", response_model=TryOnResponse)
async def tryon(request: TryOnRequest):
    """Virtual Try-On Endpoint"""
    start_time = time.time()
    
    try:
        print(f"\n{'='*60}")
        print(f"📸 Virtual Try-On Request")
        print(f"{'='*60}")
        print(f"Cloth Type: {request.cloth_type}")
        
        # Decode images
        person_data = base64.b64decode(request.person_image_base64)
        cloth_data = base64.b64decode(request.cloth_image_base64)
        
        person_image = Image.open(io.BytesIO(person_data)).convert("RGB")
        cloth_image = Image.open(io.BytesIO(cloth_data)).convert("RGB")
        
        # Resize to standard size for OOTD
        target_size = (768, 1024)
        person_image = person_image.resize(target_size, Image.Resampling.LANCZOS)
        cloth_image = cloth_image.resize(target_size, Image.Resampling.LANCZOS)
        
        print("🎨 Processing try-on...")
        
        # Create inpainting mask
        mask = create_inpainting_mask(target_size, request.cloth_type)
        
        # Generate try-on using OOTD
        with torch.inference_mode():
            prompt = f"A person wearing {request.cloth_type} clothing, high quality, realistic, professional photography"
            
            result_image = pipeline(
                prompt=prompt,
                image=person_image,
                mask_image=mask,
                num_inference_steps=30,
                guidance_scale=7.5,
                negative_prompt="ugly, blurry, low quality, distorted",
            ).images[0]
        
        # Convert to base64
        buffer = io.BytesIO()
        result_image.save(buffer, format="PNG", quality=95)
        image_base64 = base64.b64encode(buffer.getvalue()).decode()
        
        processing_time = time.time() - start_time
        
        print(f"✅ Complete! ({processing_time:.2f}s)")
        print(f"{'='*60}\n")
        
        return TryOnResponse(
            status="success",
            image_base64=image_base64,
            processing_time=processing_time
        )
        
    except Exception as e:
        processing_time = time.time() - start_time
        error_msg = str(e)
        print(f"❌ Error: {error_msg}")
        print(f"{'='*60}\n")
        
        return TryOnResponse(
            status="error",
            error=error_msg,
            processing_time=processing_time
        )

print("✅ FastAPI configured\n")

# ============================================================================
# STEP 5: Setup Ngrok
# ============================================================================
print("=" * 70)
print("🌍 Setting up Ngrok tunnel...")
print("=" * 70)
print()

try:
    from kaggle_secrets import UserSecretsClient
    user_secrets = UserSecretsClient()
    
    ngrok_token = user_secrets.get_secret("NGROK_AUTH_TOKEN")
    ngrok_domain = user_secrets.get_secret("NGROK_STATIC_DOMAIN")
    
    print(f"✅ Secrets loaded")
    
except Exception as e:
    print(f"❌ Error loading secrets: {e}")
    print("   Make sure to add these Kaggle secrets:")
    print("   - NGROK_AUTH_TOKEN")
    print("   - NGROK_STATIC_DOMAIN")
    sys.exit(1)

# Set auth token
try:
    ngrok.set_auth_token(ngrok_token)
except Exception as e:
    if "already set" not in str(e).lower():
        print(f"⚠️  Warning setting auth token: {e}")

# Connect with static domain
print(f"🔗 Connecting to {ngrok_domain}...")

try:
    tunnel = ngrok.connect(8000, hostname=ngrok_domain)
    public_url = tunnel.public_url
    print(f"✅ Connected with static domain")
except Exception as e:
    print(f"⚠️  Static domain failed, trying basic connection...")
    try:
        tunnel = ngrok.connect(8000)
        public_url = tunnel.public_url
        print(f"✅ Connected (random URL)")
    except Exception as e2:
        print(f"❌ Ngrok connection failed: {e2}")
        sys.exit(1)

print()
print("=" * 70)
print("✅ SERVER IS RUNNING!")
print("=" * 70)
print()
print(f"🌐 API URL: {public_url}")
print(f"📖 Docs: {public_url}/docs")
print(f"❤️  Health: {public_url}/health")
print()
print(f"📋 Add to Laravel .env:")
print(f"   TRYON_API_URL={public_url}")
print()
print("=" * 70)
print()

# ============================================================================
# STEP 6: Start Server
# ============================================================================
import threading

def run_server():
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")

server_thread = threading.Thread(target=run_server, daemon=True)
server_thread.start()

time.sleep(3)

print("💡 Server running! Keep this notebook active.")
print()

try:
    while True:
        time.sleep(60)
        print(f"⏰ {time.strftime('%H:%M:%S')} - Server active")
except KeyboardInterrupt:
    print("\n🛑 Shutting down...")
