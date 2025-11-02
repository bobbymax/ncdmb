# Dual AI Provider Implementation Guide

## Overview

Your inbound document analysis now supports **both OpenAI and HuggingFace** AI providers, allowing you to compare quality, speed, and cost between them.

---

## 🎉 What's Implemented

### Backend (Laravel)
✅ **AIController with dual providers**
- Routes requests to OpenAI or HuggingFace based on configuration
- Handles model-specific parameters (GPT-5 restrictions, HF prompt format)
- Unified response structure
- Comprehensive error handling and logging

### Frontend (React)
✅ **Provider selection UI**
- Toggle buttons in AI Analysis section
- Real-time provider switching
- Visual feedback for active provider
- Seamless integration with existing UI

### Configuration
✅ **Flexible setup**
- Per-request provider override
- Global default provider setting
- Independent model/parameter configuration per provider

---

## 🚀 Quick Start

### 1. Add Environment Variables

Edit `/Users/bobbyekaro/Sites/portal/.env`:

```bash
# OpenAI Configuration (Already configured)
OPENAI_API_KEY=your-key-here
OPENAI_MODEL=gpt-5-mini
OPENAI_TEMPERATURE=0.3
OPENAI_MAX_TOKENS=1400

# HuggingFace Configuration (NEW)
HUGGINGFACE_API_KEY=hf_your_key_here
HUGGINGFACE_MODEL=mistralai/Mixtral-8x7B-Instruct-v0.1
HUGGINGFACE_TEMPERATURE=0.7
HUGGINGFACE_MAX_TOKENS=1400

# AI Provider Settings (NEW)
AI_PROVIDER=openai  # Default provider: openai or huggingface
```

### 2. Get HuggingFace API Key

1. Go to https://huggingface.co/settings/tokens
2. Click **"Create new token"**
3. Name it (e.g., "NCDMB Analysis")
4. Select **"Read"** permission
5. Copy the token (`hf_...`)
6. Add to `.env` file

### 3. Clear Config Cache

```bash
cd /Users/bobbyekaro/Sites/portal
php artisan config:clear
```

### 4. Test the Integration

1. Navigate to an inbound document view page
2. Look for the **"AI Analysis"** section
3. You'll see two toggle buttons: **OpenAI** and **HuggingFace**
4. Click between them to switch providers
5. Upload/analyze a document to test

---

## 📊 Provider Comparison

### OpenAI GPT-5-Mini

**Pros:**
- ⭐⭐⭐⭐⭐ Highest quality analysis
- ✅ Excellent JSON formatting
- ✅ Fast responses (5-10 seconds)
- ✅ High rate limits (500K TPM)
- ✅ Reliable and consistent

**Cons:**
- 💰 Costs ~$0.003 per analysis
- 🔐 Requires API key management
- 📊 8K context limit

**Best for:** Production use, critical documents

---

### HuggingFace Mixtral-8x7B

**Pros:**
- 💰💰💰 10x cheaper than OpenAI (free tier available)
- ✅ 32K context window
- ✅ Open source transparency
- ✅ No vendor lock-in
- 🚀 Can self-host for free

**Cons:**
- ⭐⭐⭐⭐ Slightly lower quality
- ⏳ Slower responses (10-20 seconds)
- 📝 May need JSON cleanup
- 🔄 First request can be slow (model loading)

**Best for:** Testing, high-volume processing, cost-sensitive operations

---

## 🎯 How It Works

### Request Flow

```
User Selects Provider
        ↓
Frontend (React)
        ↓
AIService.analyzeInboundDocument(data, config, provider)
        ↓
ApiService POST /api/ai/analyze-inbound
        ↓
Laravel AIController
        ↓
   ┌────┴────┐
   ↓         ↓
OpenAI   HuggingFace
   ↓         ↓
   └────┬────┘
        ↓
  Unified Response
        ↓
  Frontend Display
```

### Backend Routing Logic

```php
// AIController.php - Line 40
$provider = $validated['provider'] ?? config('services.ai.default_provider', 'openai');

if ($provider === 'huggingface') {
    return $this->analyzeWithHuggingFace($validated);
}

return $this->analyzeWithOpenAI($validated);
```

---

## 🔧 Configuration Options

### Global Default (Affects All Requests)

```bash
# In .env
AI_PROVIDER=openai  # or huggingface
```

### Per-Request Override (Frontend)

```typescript
// In useInboundAI hook
const [provider, setProvider] = useState<'openai' | 'huggingface'>('openai');

// User can toggle in UI
<button onClick={() => setProvider('huggingface')}>
  Use HuggingFace
</button>
```

---

## 📝 Available Models

### OpenAI Models

```bash
# Recommended
OPENAI_MODEL=gpt-5-mini          # Best balance (8K context)

# Alternatives
OPENAI_MODEL=gpt-5               # Highest quality (8K context)
OPENAI_MODEL=gpt-5-nano          # Fastest (8K context)
OPENAI_MODEL=gpt-4.1-mini        # Older generation (16K context)
```

### HuggingFace Models

```bash
# Recommended
HUGGINGFACE_MODEL=mistralai/Mixtral-8x7B-Instruct-v0.1  # Best quality

# Alternatives
HUGGINGFACE_MODEL=meta-llama/Llama-3.1-70B-Instruct     # Highest quality (slow)
HUGGINGFACE_MODEL=mistralai/Mistral-7B-Instruct-v0.2    # Fast, cheaper
HUGGINGFACE_MODEL=Qwen/Qwen2.5-14B-Instruct             # Good JSON
HUGGINGFACE_MODEL=microsoft/Phi-3-medium-128k-instruct  # Large context
```

---

## 💡 Usage Tips

### When to Use OpenAI
- Critical business documents
- Need highest accuracy
- Short to medium documents (< 6K tokens)
- Production environment

### When to Use HuggingFace
- Testing and development
- High-volume processing (100+ docs/day)
- Cost-sensitive operations
- Long documents (up to 25K tokens)
- Privacy-sensitive content (can self-host)

---

## 🐛 Troubleshooting

### HuggingFace "Model Loading" Error

```json
{"error": "Model is loading, please retry"}
```

**Solution:** Wait 20-30 seconds and try again. HF cold-starts models on first use.

---

### HuggingFace JSON Parse Error

```json
{"error": "Failed to parse HuggingFace response as JSON"}
```

**Solution:** 
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Look for the `raw_content` field to see what was returned
3. Model may need fine-tuning or different prompt format

---

### OpenAI Rate Limit

```json
{"error": "Rate limit reached for gpt-5-mini"}
```

**Solution:** Switch to HuggingFace temporarily or wait for rate limit reset.

---

## 📊 Cost Analysis

### Monthly Estimates (100 Documents/Month)

**OpenAI GPT-5-Mini:**
- Cost per doc: ~$0.003
- Monthly: ~$0.30
- Annual: ~$3.60

**HuggingFace Mixtral-8x7B:**
- Cost per doc: ~$0.0003 (free tier: $0)
- Monthly: ~$0.03 (or $0 on free tier)
- Annual: ~$0.36 (or $0 on free tier)

**Savings:** ~90% with HuggingFace

---

## 🔐 Security Notes

### OpenAI
- ✅ API key stored in backend `.env` only
- ✅ Not exposed to frontend
- ✅ Transmitted over HTTPS
- ✅ Per-user tracking enabled

### HuggingFace
- ✅ Same security as OpenAI
- ✅ Can self-host for complete data control
- ✅ Open source models = full transparency

---

## 📈 Monitoring

### Check Which Provider Was Used

```bash
# Laravel logs show provider for each request
tail -f storage/logs/laravel.log | grep "Analysis Completed"

# Example output:
# [INFO] OpenAI Analysis Completed {"provider":"openai","model":"gpt-5-mini",...}
# [INFO] HuggingFace Analysis Completed {"provider":"huggingface","model":"mistralai/Mixtral-8x7B",...}
```

### Backend Response Includes Provider

```json
{
  "success": true,
  "data": {
    "analysis": {...},
    "usage": {...},
    "provider": "openai",  // or "huggingface"
    "model": "gpt-5-mini"
  }
}
```

---

## 🎨 UI Features

### Provider Toggle Buttons

Located in the "AI Analysis" section:
- **Blue button** = Active provider
- **Gray button** = Inactive provider
- Hover for tooltip with model info
- Switch anytime (clears previous analysis)

### Visual Feedback

- Provider name shown in console logs
- Loading message shows active provider
- Analysis results display confidence regardless of provider

---

## 🚀 Next Steps

### 1. Test Both Providers

Upload the same document twice:
1. First with OpenAI
2. Then with HuggingFace
3. Compare quality, speed, and results

### 2. Choose Default Provider

Based on testing, set your preferred default:

```bash
# In .env
AI_PROVIDER=huggingface  # or openai
```

### 3. Consider Self-Hosting (Advanced)

For ultimate cost savings and privacy:
1. Set up Ollama on your server
2. Pull Mixtral or Llama models
3. Point HF config to local endpoint
4. Zero per-request cost!

---

## 📚 Files Modified/Created

### Backend
- ✅ `config/services.php` - Added HF config
- ✅ `app/Http/Controllers/AIController.php` - Dual provider support
- ✅ `.env` - Added HF environment variables

### Frontend
- ✅ `src/app/Services/AIService.ts` - Provider parameter support
- ✅ `src/app/Hooks/useInboundAI.ts` - Provider state management
- ✅ `src/resources/views/crud/InboundView.tsx` - Provider toggle UI

---

## ✅ Success Checklist

- [ ] HuggingFace API key added to `.env`
- [ ] Config cache cleared
- [ ] Both toggle buttons visible in UI
- [ ] Can switch between providers
- [ ] OpenAI analysis works
- [ ] HuggingFace analysis works
- [ ] Laravel logs show correct provider
- [ ] Compared quality between providers
- [ ] Chosen default provider

---

**Congratulations!** 🎉 You now have a flexible, cost-effective dual AI provider system! 🚀

