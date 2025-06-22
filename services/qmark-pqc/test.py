import oqs

with oqs.Signature('Dilithium2') as signer:
    # Keypair generation
    public_key, secret_key = signer.generate_keypair()



# import oqs

# # Create signature object
# with oqs.Signature('Dilithium2') as signer:
#     # Keypair generation
#     public_key = signer.generate_public_key()
#     secret_key = signer.generate_secret_key()
    
#     message = b"Hello from QMark"
    
#     # Signing
#     signature = signer.sign(message, secret_key)

#     # Verification
#     valid = signer.verify(message, signature, public_key)
#     print("✅ Valid Signature" if valid else "❌ Invalid")
