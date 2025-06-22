import base64
from fastapi import FastAPI
from fastapi.responses import JSONResponse
from pydantic import BaseModel
import oqs
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey, Ed25519PublicKey
from cryptography.hazmat.primitives import serialization
from cryptography.exceptions import InvalidSignature
from pathlib import Path
import os

app = FastAPI()

KEY_DIR = Path(__file__).resolve().parent / "keys"
KEY_DIR.mkdir(parents=True, exist_ok=True)

# Constants for signature sizes
SIGNATURE_VERSIONS = {
    "2024-QMARK-V1": {
        "pqc_alg": "Dilithium2",
        "pqc_len": 2420,
        "classic_alg": "Ed25519",
        "classic_len": 64,
    }
}

pqc_instance = oqs.Signature("Dilithium2")
# Load versioned keys (for demo, hardcoded here)
VERSION_KEYS = {
    "2024-QMARK-V1": {
        "pqc": pqc_instance,
        "pqc_keypair": None,
        "ed_priv": None,
        "ed_pub": None,
    }
}

pqc_alg_name = SIGNATURE_VERSIONS["2024-QMARK-V1"]["pqc_alg"]
pqc_alg_name_lower = pqc_alg_name.lower()

classic_alg_name = SIGNATURE_VERSIONS["2024-QMARK-V1"]["classic_alg"]
classic_alg_name_lower = classic_alg_name.lower()

print("CWD")
print(os.getcwd())
# Generate and cache keys
# for version, data in VERSION_KEYS.items():
#     signer = data["pqc"]
#     pub = signer.generate_keypair()
#     data["pqc_signer"] = pub

#     ed_priv = Ed25519PrivateKey.generate()
#     ed_pub = ed_priv.public_key()
#     data["ed_priv"] = ed_priv
#     data["ed_pub"] = ed_pub

class SignRequest(BaseModel):
    data: str
    key_id: str = "2024-QMARK-V1"

class VerifyRequest(BaseModel):
    data: str
    signature: str
    key_id: str = "2024-QMARK-V1"

def get_key_path(alg_name: str, key_type: str):
    return f"{alg_name.lower()}_{key_type}.key"

def save_base64(path: Path, data: bytes):
    with open(path, "wb") as f:
        f.write(base64.b64encode(data))

def load_base64(path: Path) -> bytes:
    with open(path, "rb") as f:
        return base64.b64decode(f.read())

def load_or_generate_pqc_keys():
    #pqc_alg_name = SIGNATURE_VERSIONS["2024-QMARK-V1"]["pqc_alg"]
    pub_path = Path(pqc_alg_name_lower+"_public.key")
    priv_path = Path(pqc_alg_name_lower+"_private.key")
    signer = oqs.Signature(pqc_alg_name)
    if pub_path.exists() and priv_path.exists():
        public_key = load_base64(pub_path)
        private_key = load_base64(priv_path)
        print("pqc_found")
    else:
        public_key = signer.generate_keypair()
        private_key = signer.export_secret_key()
        try:
            with open("demofile.txt", "a") as f:
                f.write("Now the file has more content!")
        except:
            print("An exception occurred")
        save_base64(pub_path, public_key)
        save_base64(priv_path, private_key)
        print("pqc_saved")

    return signer, public_key, private_key

def load_or_generate_classic_keys():
    pub_path = Path(classic_alg_name_lower+"_public.key")
    priv_path = Path(classic_alg_name_lower+"_private.key")

    #TODO update the KEY CLass name
    if pub_path.exists() and priv_path.exists():
        public_key = Ed25519PublicKey.from_public_bytes(load_base64(pub_path))
        private_key = Ed25519PrivateKey.from_private_bytes(load_base64(priv_path))
    else:
        private_key = Ed25519PrivateKey.generate()
        public_key = private_key.public_key()
        save_base64(priv_path, private_key.private_bytes(
            encoding=serialization.Encoding.Raw,
            format=serialization.PrivateFormat.Raw,
            encryption_algorithm=serialization.NoEncryption()
        ))
        save_base64(pub_path, public_key.public_bytes(
            encoding=serialization.Encoding.Raw,
            format=serialization.PublicFormat.Raw
        ))

    return public_key, private_key

pqc_signer, pqc_pub_key, pqc_pri_key = load_or_generate_pqc_keys()
classic_pub_key, classic_pri_key = load_or_generate_classic_keys()

print(pqc_signer)    
@app.post("/sign")
async def sign_payload(request: SignRequest):
    version = request.key_id
    version_info = SIGNATURE_VERSIONS.get(version)
    version_keys = VERSION_KEYS.get(version)

    if not (version_info and version_keys):
        return JSONResponse(status_code=400, content={"error": "Unknown key_id"})

    try:
        # Decode base64 payload
        data = base64.b64decode(request.data)
        print("1")
        # Sign with PQC
        sig_pqc = pqc_signer.sign_with_ctx_str(data, pqc_pri_key) #Signature(pqc_alg_name).import_secret_key(pqc_pri_key)
        #sig_pqc = pqc_signer.sign(data)
        #with version_keys["pqc"] as pqc:
        #    sig_pqc = pqc.sign(data)
        print("2")
        # Sign with Ed25519
        #ed_priv = Ed25519PrivateKey.from_private_bytes(version_keys["ed_priv"])
        sig_classic =  classic_pri_key.sign(data) #version_keys["ed_priv"].sign(data) #ed_priv.sign(data)
        print("3")
        # Concatenate the hybrid signature
        hybrid_signature = sig_pqc + sig_classic
        encoded_signature = base64.b64encode(hybrid_signature).decode("utf-8")
        print("4")
        return {
            "signature": encoded_signature,
            "key_id": version
        }

    except Exception as e:
        return JSONResponse(status_code=500, content={"error": str(e)})
# def sign(request: SignRequest):
#     if request.key_id not in VERSION_KEYS:
#         return {"error": "Unsupported key_id"}

#     data = request.data.encode()
#     keys = VERSION_KEYS[request.key_id]

#     pqc_signature = keys["pqc"].sign(data)
#     ed_signature = keys["ed_priv"].sign(data)

#     hybrid_signature = base64.b64encode(pqc_signature + ed_signature).decode()
#     return {"signature": hybrid_signature, "key_id": request.key_id}

@app.post("/verify")
async def verify_signature(payload: VerifyRequest):
    version = payload.key_id
    version_info = SIGNATURE_VERSIONS.get(version)
    version_keys = VERSION_KEYS.get(version)

    if not (version_info and version_keys):
        return JSONResponse(status_code=400, content={"valid": False, "error": "Unknown key_id"})

    try:
        pqc_len = version_info["pqc_len"]
        classic_len = version_info["classic_len"]

        signature = base64.b64decode(payload.signature)
        data = base64.b64decode(payload.data)

        sig_pqc = signature #[:pqc_len]

        sig_classic = signature
        #sig_classic = signature[pqc_len:pqc_len + classic_len]
        print("pqc algo")
        # PQC Verification
        with version_keys["pqc"] as pqc:
            valid_pqc = pqc.verify(data, sig_pqc, pqc_pub_key)#pqc.verify(data, sig_pqc, version_keys["pqc_pub"])
        print("PQC")
        print(valid_pqc)
        # Classical Verification
        try:
            print("5")
            data["ed_pub"].verify(sig_classic, data)
            #ed_pub = Ed25519PublicKey.from_public_bytes(version_keys["ed_pub"])
            print("6")
            #ed_pub.verify(sig_classic, data)
            valid_classic = True
        except InvalidSignature:
            valid_classic = False

        return {"valid": valid_pqc and valid_classic}

    except Exception as e:
        return JSONResponse(status_code=500, content={"valid": False, "error": str(e)})

@app.post("/generate-keys")
def generate_keys():
    try:
        version = "2024-QMARK-V1"

        # --- Generate Dilithium2 keys ---
        pqc = pqc_instance
        pqc_pub = pqc.generate_keypair()
        pqc_priv = pqc.export_secret_key()

        with open(KEY_DIR / f"dilithium2_private.key", "wb") as f:
            f.write(pqc_priv)
        with open(KEY_DIR / f"dilithium2_public.key", "wb") as f:
            f.write(pqc_pub)

        # --- Generate Ed25519 keys ---
        ed_priv = Ed25519PrivateKey.generate()
        ed_pub = ed_priv.public_key()

        ed_priv_bytes = ed_priv.private_bytes(
            encoding=serialization.Encoding.Raw,
            format=serialization.PrivateFormat.Raw,
            encryption_algorithm=serialization.NoEncryption()
        )
        ed_pub_bytes = ed_pub.public_bytes(
            encoding=serialization.Encoding.Raw,
            format=serialization.PublicFormat.Raw
        )

        with open(KEY_DIR / f"classic_private.key", "wb") as f:
            f.write(ed_priv_bytes)
        with open(KEY_DIR / f"classic_public.key", "wb") as f:
            f.write(ed_pub_bytes)

        # --- Update VERSION_KEYS at runtime ---
        #VERSION_KEYS[version]["pqc_keypair"] = (pqc_priv, pqc_pub)
        #VERSION_KEYS[version]["ed_priv"] = ed_priv
        #VERSION_KEYS[version]["ed_pub"] = ed_pub

        return {
            "status": "success",
            "message": f"Keys generated and loaded for version {version}",
            "public_keys": {
                "dilithium2": pqc_pub.hex(),
                "ed25519": ed_pub_bytes.hex()
            }
        }

    except Exception as e:
        return {"status": "error", "message": str(e)}    
# def verify(request: VerifyRequest):
#     if request.key_id not in SIGNATURE_VERSIONS:
#         return {"valid": False, "error": "Unknown key_id"}

#     data = request.data.encode()

#     print(data)
#     print(request.key_id)
#     print(request.signature)

#     config = SIGNATURE_VERSIONS[request.key_id]
#     keys = VERSION_KEYS[request.key_id]
#     pqc_signer = keys["pqc"]
#     pqc_pub = pqc_signer.generate_keypair()

#     full_signature = base64.b64decode(request.signature.encode())
#     pqc_len = config["pqc_len"]
#     classic_len = config["classic_len"]

#     pqc_sig = full_signature[:pqc_len]
#     ed_sig = full_signature[pqc_len:pqc_len + classic_len]

#     try:
#         pqc_valid = pqc_signer.verify(data, pqc_sig, pqc_pub)
#     except:
#         pqc_valid = False

#     print(pqc_valid)

#     try:
#         keys["ed_pub"].verify(ed_sig, data)
#         ed_valid = True
#     except:
#         ed_valid = False

#     print(ed_valid)

#     return {"valid": pqc_valid and ed_valid}

# DILITHIUM2_SIG_LEN = 2420
# ED25519_SIG_LEN = 64
# # Generate keypair on server startup
# signer = oqs.Signature("Dilithium2")
# PUBLIC_KEY = signer.generate_keypair()

# ED_PRIV_KEY = Ed25519PrivateKey.generate()
# ED_PUB_KEY = ED_PRIV_KEY.public_key()

# # --- Request Schema ---
# class SignRequest(BaseModel):
#     data: str

# class VerifyRequest(BaseModel):
#     data: str
#     signature: str

# @app.get("/")
# def root():
#     return {"message": "QMark PQC Service is online."}

# @app.post("/sign")
# def sign(req: SignRequest):
#     msg = req.data.encode()

#     pqc_signature = signer.sign(msg)
#     ed_signature = ED_PRIV_KEY.sign(msg)

#     hybrid_signature = base64.b64encode(pqc_signature + ed_signature).decode()
#     return {"signature": hybrid_signature}

# @app.post("/verify")
# def verify(req: VerifyRequest):
#     msg = req.data.encode()
#     full_signature = base64.b64decode(req.signature.encode())

#     pqc_signature = full_signature[:DILITHIUM2_SIG_LEN]
#     ed_signature = full_signature[DILITHIUM2_SIG_LEN:]

#     pqc_valid = pqc_signature.verify(msg, pqc_signature, PUBLIC_KEY)
#     try:
#         ED_PUB_KEY.verify(ed_signature, msg)
#         ed_valid = True
#     except Exception:
#         ed_valid = False


#     return {"valid": pqc_valid and ed_valid}

# @app.get("/public-key")
# def get_public_key():
#     return {"publicKey": base64.b64encode(PUBLIC_KEY).decode()}

@app.get("/supported")
def get_supported_algorithms():
    return {
        "supported_signature_algorithms": oqs.get_enabled_sig_mechanisms()
    }