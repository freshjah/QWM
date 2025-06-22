from fastapi import FastAPI, File, UploadFile, Form
from fastapi.responses import StreamingResponse, JSONResponse
import io
import base64
import fitz  # PyMuPDF
import magic
from PIL import Image
import numpy as np
from scipy.fftpack import dct, idct
import json

app = FastAPI()

def detect_mime(file_bytes):
    return magic.from_buffer(file_bytes, mime=True)

def encode_payload(payload_dict):
    json_str = json.dumps(payload_dict)
    b64 = base64.b64encode(json_str.encode("utf-8")).decode("ascii")
    length = len(b64)
    return f"{length}:{b64}"

def decode_payload(encoded):
    try:
        length_str, b64 = encoded.split(":", 1)
        length = int(length_str)
        json_str = base64.b64decode(b64[:length]).decode("utf-8")
        return json.loads(json_str)
    except Exception as e:
        raise ValueError(f"Decoding failed: {str(e)}")

def embed_image_watermark(image_bytes, payload):
    img = Image.open(io.BytesIO(image_bytes)).convert("L")
    arr = np.array(img, dtype=np.float32)
    dct_arr = dct(dct(arr.T, norm="ortho").T, norm="ortho")
    payload_bin = ''.join(format(byte, '08b') for byte in payload.encode("utf-8"))
    flat = dct_arr.flatten()
    for i, bit in enumerate(payload_bin):
        flat[i] += 1 if bit == '1' else -1
    watermarked_dct = flat.reshape(dct_arr.shape)
    idct_arr = idct(idct(watermarked_dct.T, norm="ortho").T, norm="ortho")
    watermarked = Image.fromarray(np.clip(idct_arr, 0, 255).astype(np.uint8))
    output = io.BytesIO()
    watermarked.save(output, format="PNG")
    output.seek(0)
    return output

def extract_image_watermark(image_bytes, length=2048):
    img = Image.open(io.BytesIO(image_bytes)).convert("L")
    arr = np.array(img, dtype=np.float32)
    dct_arr = dct(dct(arr.T, norm="ortho").T, norm="ortho")
    flat = dct_arr.flatten()
    bits = ['1' if val % 2 > 0 else '0' for val in flat[:length * 8]]
    payload_bin = ''.join(bits)
    chars = [chr(int(payload_bin[i:i+8], 2)) for i in range(0, len(payload_bin), 8)]
    return ''.join(chars)

def embed_pdf_watermark(pdf_bytes, payload):
    doc = fitz.open(stream=pdf_bytes, filetype="pdf")
    metadata = doc.metadata
    metadata["keywords"] = payload
    doc.set_metadata(metadata)
    output = io.BytesIO()
    doc.save(output)
    output.seek(0)
    return output

def extract_pdf_watermark(pdf_bytes):
    doc = fitz.open(stream=pdf_bytes, filetype="pdf")
    return doc.metadata.get("keywords", "")

@app.post("/embed")
async def embed(file: UploadFile = File(...), payload: str = Form(...)):
    file_bytes = await file.read()
    mime = detect_mime(file_bytes)
    if mime.startswith("image/"):
        result = embed_image_watermark(file_bytes, payload)
        return StreamingResponse(result, media_type="image/png")
    elif mime == "application/pdf":
        result = embed_pdf_watermark(file_bytes, payload)
        return StreamingResponse(result, media_type="application/pdf")
    return JSONResponse(status_code=415, content={"error": f"Unsupported file type: {mime}"})

@app.post("/extract")
async def extract(file: UploadFile = File(...)):
    file_bytes = await file.read()
    mime = detect_mime(file_bytes)
    if mime.startswith("image/"):
        extracted = extract_image_watermark(file_bytes)
        return {"payload": extracted}
    elif mime == "application/pdf":
        extracted = extract_pdf_watermark(file_bytes)
        return {"payload": extracted}
    return JSONResponse(status_code=415, content={"error": f"Unsupported file type: {mime}"})