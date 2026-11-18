# Google Gemini Virtual Try-On API - Usage Guide

## Quick Start

### 1. Setup
```bash
# 1. Get API key from https://aistudio.google.com/apikey
# 2. Add to .env
GEMINI_API_KEY=your_actual_key_here

# 3. Ensure storage directory exists
mkdir -p storage/app/public/tryons

# 4. Run artisan command to create public symlink (if needed)
php artisan storage:link
```

### 2. API Endpoint

```
POST /api/tryon
```

**Authentication:** Required (Bearer token or session)

**Content-Type:** `multipart/form-data`

### 3. Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `person_image` | File | Yes | Image of person (JPG, PNG, WebP), max 5MB |
| `cloth_image` | File | Yes | Image of garment (JPG, PNG, WebP), max 5MB |
| `cloth_type` | String | No | Category: `upper`, `lower`, or `overall` (default: `upper`) |

### 4. cURL Example

```bash
curl -X POST http://tahiconnect.test/api/tryon \
  -H "Authorization: Bearer {YOUR_BEARER_TOKEN}" \
  -F "person_image=@person.jpg" \
  -F "cloth_image=@shirt.jpg" \
  -F "cloth_type=upper"
```

### 5. JavaScript/Fetch Example

```javascript
const formData = new FormData();
formData.append('person_image', personImageFile);
formData.append('cloth_image', clothImageFile);
formData.append('cloth_type', 'upper');

const response = await fetch('/api/tryon', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData,
});

const data = await response.json();
if (data.status === 'success') {
  console.log('Try-on image:', data.result_url);
  // Display the image
  const img = new Image();
  img.src = data.result_url;
  document.body.appendChild(img);
} else {
  console.error('Error:', data.message);
}
```

### 6. Response

**Success (200 OK):**
```json
{
  "status": "success",
  "result_url": "https://tahiconnect.test/storage/tryons/tryon_550e8400-e29b-41d4-a716-446655440000.jpg"
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "status": "error",
  "message": "Rate limit reached. Please wait 30 seconds and try again."
}
```

### 7. Possible Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| `GEMINI_API_KEY is not configured` | API key missing in .env | Add your API key to .env |
| `Rate limit reached. Please wait 30 seconds` | Free tier quota exceeded | Wait 30 seconds and retry, or upgrade API plan |
| `No image data returned from Google Gemini API` | API didn't generate image | Check input images are clear, retry |
| Validation error | Invalid file format or size | Use JPG/PNG/WebP, max 5MB |
| `Failed to save generated image to storage` | Storage permission issue | Check storage/app/public/tryons is writable |

## Testing

### Using Artisan Tinker

```php
php artisan tinker

$service = new \App\Services\VirtualTryOnService();
$personFile = new Illuminate\Http\UploadedFile(
  'path/to/person.jpg',
  'person.jpg',
  'image/jpeg',
  null,
  true
);
$clothFile = new Illuminate\Http\UploadedFile(
  'path/to/cloth.jpg',
  'cloth.jpg',
  'image/jpeg',
  null,
  true
);
$url = $service->generateTryOn($personFile, $clothFile, 'upper');
echo $url;
```

## Performance Notes

- **Typical response time:** 30-45 seconds
- **Timeout:** 60 seconds
- **Storage:** Results stored in `storage/app/public/tryons/`
- **Naming:** UUIDs prevent collisions
- **Cleanup:** Set up a scheduled job to clean old files after 30 days if desired

## Limitations

- Google Gemini free tier has rate limits
- Maximum file size: 5MB per image
- Supported formats: JPG, PNG, WebP
- Person image should be a clear, full-body photo
- Garment image should show the item clearly

## Next Steps

1. Integrate endpoint into your Vue/Livewire component
2. Build UI for image upload
3. Display results in gallery
4. Add progress indicator (60-second timeout)
5. Implement retry logic for rate limits
