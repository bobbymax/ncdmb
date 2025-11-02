# AI Service Setup Guide

## Overview

This guide explains how to set up the AI service for inbound document analysis.

## Backend Setup (Laravel)

### 1. Add Environment Variables

Add the following to your `/Users/bobbyekaro/Sites/portal/.env` file:

```bash
# OpenAI Configuration
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.3
OPENAI_MAX_TOKENS=2000
```

### 2. Get OpenAI API Key

1. Go to https://platform.openai.com/
2. Sign in or create an account
3. Navigate to API Keys section
4. Create a new secret key
5. Copy the key and add it to your `.env` file

### 3. Verify Configuration

Run this artisan command to test:

```bash
php artisan tinker
>>> config('services.openai.api_key')
# Should output your API key
```

## Frontend Setup (React)

No additional setup needed! The frontend is already configured to use the backend proxy.

## Testing the Integration

### 1. Upload Test Document

```bash
1. Navigate to http://localhost:3000/desk/inbounds/create
2. Fill in sender information
3. Upload a PDF document
4. Submit the form
```

### 2. View AI Analysis

```bash
1. Navigate to the inbound document view page
2. Wait for PDF to merge (1-2 seconds)
3. AI analysis will automatically trigger
4. Results will appear in the right column
```

### 3. Check Logs

Monitor Laravel logs for AI requests:

```bash
tail -f storage/logs/laravel.log | grep "AI Analysis"
```

## API Endpoint

### POST `/api/ai/analyze-inbound`

**Authentication**: Required (Sanctum)

**Request Body**:
```json
{
  "documentText": "Full extracted text from PDFs...",
  "boardDescription": "NCDMB mission and objectives...",
  "senderInfo": {
    "name": "Company Name",
    "email": "contact@company.com",
    "phone": "+234XXXXXXXXXX"
  },
  "config": {
    "model": "gpt-4",
    "temperature": 0.3,
    "maxTokens": 2000
  }
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "analysis": {
      "summary": "Document summary...",
      "keyFeatures": ["Feature 1", "Feature 2"],
      "organizationalBenefits": ["Benefit 1", "Benefit 2"],
      "documentType": "Proposal",
      "confidence": 0.95,
      "urgency": "medium",
      "suggestedActions": ["Action 1", "Action 2"]
    },
    "usage": {
      "prompt_tokens": 1200,
      "completion_tokens": 400,
      "total_tokens": 1600
    }
  }
}
```

## Cost Estimation

### OpenAI GPT-4 Pricing (as of 2024)
- Input: $0.03 per 1K tokens
- Output: $0.06 per 1K tokens

### Typical Inbound Document Analysis
- Input tokens: ~1,500 tokens (document + context)
- Output tokens: ~500 tokens (analysis)
- **Cost per analysis**: ~$0.075 (less than 8 cents)

### Monthly Estimates
- 100 documents/month: ~$7.50
- 500 documents/month: ~$37.50
- 1000 documents/month: ~$75.00

## Troubleshooting

### Error: "OpenAI API key not configured"
**Solution**: Add `OPENAI_API_KEY` to backend `.env` file

### Error: "CORS policy blocked"
**Solution**: Already fixed! Frontend now calls backend proxy

### Error: "Could not extract text from PDFs"
**Solution**: Documents may be scanned images. Consider adding OCR (Tesseract.js)

### Error: "Request timeout"
**Solution**: Increase timeout in config or split large documents

### Error: "Invalid API key"
**Solution**: Verify API key is correct and has billing enabled

## Security Notes

1. ✅ API key stored on backend only
2. ✅ Protected by Laravel auth middleware
3. ✅ User ID logged with each request
4. ✅ Request/response logging enabled
5. ✅ Timeout protection (60 seconds)

## Rate Limiting (Optional)

Add rate limiting to prevent abuse:

```php
// In routes/api.php
Route::middleware(['throttle:10,1'])->post('ai/analyze-inbound', ...);
// Allows 10 requests per minute
```

## Caching (Optional)

Cache AI responses to save costs:

```php
// In AIController.php
$cacheKey = 'ai_analysis_' . md5($validated['documentText']);
$analysis = Cache::remember($cacheKey, 3600, function () use ($validated) {
    // ... make OpenAI request
});
```

## Files Created/Modified

### Backend (Laravel)
- ✅ `app/Http/Controllers/AIController.php` (NEW)
- ✅ `routes/api.php` (MODIFIED - added AI route)
- ✅ `config/services.php` (MODIFIED - added OpenAI config)

### Frontend (React)
- ✅ `src/app/Services/AIService.ts` (MODIFIED - calls backend instead of OpenAI)

## Next Steps

1. Add `OPENAI_API_KEY` to backend `.env`
2. Clear Laravel config cache: `php artisan config:clear`
3. Test with a sample inbound document
4. Monitor costs on OpenAI dashboard
5. Consider adding rate limiting for production

---

**Setup Complete!** The AI service is now properly configured to work through your Laravel backend. 🎉

