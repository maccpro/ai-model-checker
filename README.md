# AI Model Strength Checker & Benchmarking Suite

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/License-GPL_v3-blue.svg?style=for-the-badge" alt="GPL-3.0 License">
</p>

A universal, high-performance **AI Model Strength Checker & Benchmarking Suite** built with **Laravel 13**, **Tailwind CSS**, and **Alpine.js**. Seamlessly connect to any OpenAI-compatible provider (cloud or local), automatically discover available models, and test capabilities side-by-side with real-time streaming and microsecond performance telemetry.

---

## ⚡ GitHub Repository Description (Under 350 Chars)

```text
Universal AI Model Strength Checker & Benchmarking Suite built with Laravel 13 & Tailwind CSS. Supports any OpenAI-compatible Base URL, real-time SSE streaming, TTFT & TPS metrics, side-by-side battle arena, and pre-configured strength benchmark suites.
```

---

## 🚀 Key Features

- **🌐 Universal Endpoint Compatibility**: Connect to any OpenAI-compatible server (e.g. OpenAI, OpenRouter, Groq, DeepSeek, Local Ollama, LM Studio, vLLM, LiteLLM) simply by supplying a `Base URL` and optional `API Key`. No hardcoded provider lock-in.
- **🔍 Auto Model Discovery**: Automatically queries `{Base URL}/models` through a high-speed backend proxy (preventing CORS issues), normalizes schemas, and sorts all models alphabetically in searchable dropdowns.
- **🧪 Playground Mode**: Interactive single-model testing with real-time Server-Sent Events (SSE) streaming, markdown rendering, and syntax-highlighted code blocks.
- **⚔️ Side-by-Side Arena (Battle Mode)**: Select Model A and Model B to benchmark against the exact same prompt simultaneously with real-time side-by-side telemetry comparison.
- **🏆 Strength Benchmark Suite**: Pre-configured industry-standard challenge categories:
  - **Multi-step Logic & Math**: Sally's sisters deduction puzzle testing cognitive traps.
  - **Algorithm & Code Quality**: PHP 8.5 expand-around-center palindrome algorithm with multibyte UTF-8 safety.
  - **Negative Constraints (Lipogram)**: Multi-sentence generation omitting the letter "e".
  - **Strict JSON Compliance**: Structured output validation without markdown fences or pleasantries.
  - **Speed & Throughput**: Sustained sequential counting measuring peak Tokens Per Second (TPS).
- **💡 Categorized Demo Prompts & 1-Click Combos**:
  - **System Prompt Personas**: Senior Architect, Logic Tutor, Strict JSON Bot, Bangla Linguist, Security Auditor, Minimalist.
  - **User Test Prompts**: Logic puzzles, coding algorithms, negative constraints, JSON extraction, speed tests, localization, and SQL injection audits.
  - **1-Click Paired Combos**: Load both system persona and matching challenge prompt with a single click.
- **📊 Real-Time Performance HUD**:
  - **TTFT (Time to First Token)** in milliseconds.
  - **TPS (Tokens Per Second)** throughput calculation.
  - **Total Latency / Duration** in seconds.
  - **Token Usage Breakdown**: Prompt tokens, completion tokens, and total tokens.
- **💾 Saved Endpoints Library**: Store your custom Base URLs and API Keys with custom nicknames in local SQLite storage for instant recall.
- **📜 Past Benchmark History**: Inspect past benchmark runs, compare outputs, export to JSON, or purge logs.

---

## 🏗️ Architecture & Design Patterns

- **Clean Layered Architecture**:
  - **Controllers**: Thin controllers (`AiModelController`, `BenchmarkController`) handling HTTP protocol concerns only.
  - **Form Requests**: Strict validation (`FetchModelsRequest`, `RunBenchmarkRequest`, `SaveEndpointRequest`).
  - **Service Layer**: Dedicated domain services (`AiClientService`, `BenchmarkService`, `EndpointService`).
  - **Reversible Database Migrations**: Standard Laravel SQLite migrations with full `up()` and `down()` support.
- **Zero Duplicate Code**: Shared utility methods and encapsulated API proxying.
- **Zero CORS Hassle**: Server-side proxy guarantees requests to local servers (`localhost:11434`) or cloud endpoints avoid browser CORS restrictions.

---

## 💻 Tech Stack

- **Backend**: Laravel 13, PHP 8.5.10
- **Database**: SQLite
- **Frontend**: Tailwind CSS, Alpine.js, Lucide Icons, Marked.js
- **Environment**: Laravel Herd / Nginx / PHP CLI

---

## 🛠️ Quick Installation Guide

### Prerequisites
- PHP >= 8.3 (PHP 8.5 recommended)
- Composer >= 2.x
- SQLite extension enabled (`pdo_sqlite`)

### 1. Clone the repository
```bash
git clone git@github.com:maccpro/ai-model-checker.git
cd ai-model-checker
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Ensure your `.env` contains:
```env
APP_NAME="AI Model Strength Checker"
APP_URL=http://model-test.test
DB_CONNECTION=sqlite
```

### 4. Run Database Migrations
```bash
touch database/database.sqlite
php artisan migrate
```

### 5. Start the Application
- **With Laravel Herd**: Park or link the folder — instantly available at `http://ai-model-checker.test` or `http://model-test.test`.
- **Or with Artisan CLI**:
  ```bash
  php artisan serve
  ```
  Visit `http://localhost:8000`.

---

## 🧪 Running Automated Tests

Run the full PHPUnit test suite:
```bash
php artisan test
```

Code formatting with Laravel Pint:
```bash
vendor/bin/pint --format agent
```

---

## 📄 License

This project is open-source software licensed under the **GNU General Public License v3.0** (GPL-3.0). See the [LICENSE](LICENSE) file for more information.
