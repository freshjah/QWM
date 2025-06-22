FROM python:3.10-slim

# Install build dependencies
RUN apt-get update && apt-get install -y \
    cmake \
    git \
    build-essential \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Clone liboqs-python with submodules
WORKDIR /opt
RUN git clone --recursive https://github.com/open-quantum-safe/liboqs-python.git

# Install liboqs-python into the global site-packages
WORKDIR /opt/liboqs-python
RUN pip install .

# Set up working directory for your FastAPI app
WORKDIR /app
COPY ./app ./app
COPY requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

EXPOSE 8000
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
