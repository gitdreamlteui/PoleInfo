from fastapi import FastAPI, Request
from fastapi.responses import RedirectResponse
import uvicorn

redirect_app = FastAPI()

@redirect_app.middleware("http")
async def redirect_to_https(request: Request, call_next):
    url = request.url.replace(scheme="https", netloc=f"{request.url.hostname}:8443")
    return RedirectResponse(str(url))

if __name__ == "__main__":
    uvicorn.run(redirect_app, host="0.0.0.0", port=80)
