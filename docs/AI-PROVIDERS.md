# AI PROVIDERS

TechMind memakai driver OpenAI-compatible (`AI_DRIVER=openai_compatible`) dengan endpoint `POST {AI_BASE_URL}/chat/completions`.

## Konfigurasi

- `AI_DRIVER`: `template` (default, offline) atau `openai_compatible`.
- `AI_BASE_URL`: base URL provider (tanpa `/chat/completions`).
- `AI_API_KEY`: API key.
- `AI_MODEL`: nama model.
- `AI_TIMEOUT`: timeout detik (default 6).

Jika `AI_API_KEY` kosong atau `AI_DRIVER=template`, sistem langsung memakai template tanpa HTTP.

## Contoh (verifikasi dengan `php artisan techmind:ai-check`)

- OpenAI: base URL `https://api.openai.com/v1`, model `<cek dokumentasi resmi>`. Docs: https://platform.openai.com/docs
- Gemini (OpenAI-compatible): base URL `https://generativelanguage.googleapis.com/v1beta/openai`, model `<cek dokumentasi resmi>`. Docs: https://ai.google.dev/gemini-api/docs/openai
- Groq: base URL `https://api.groq.com/openai/v1`, model `<cek dokumentasi resmi>`. Docs: https://console.groq.com/docs
- OpenRouter: base URL `https://openrouter.ai/api/v1`, model `<cek dokumentasi resmi>`. Docs: https://openrouter.ai/docs
- Ollama (lokal): base URL `http://localhost:11434/v1`, model `<cek dokumentasi resmi>`. Docs: https://ollama.com/docs

Jangan menebak nama model. Verifikasi selalu dengan `php artisan techmind:ai-check`.
