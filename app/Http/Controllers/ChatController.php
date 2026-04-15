<?php

namespace App\Http\Controllers;

use App\Models\PohaciConversation;
use App\Models\PohaciMessage;
use Illuminate\Http\Request;
use App\Services\GroqService;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected GroqService $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /**
     * Handle semua jenis chat: text, file, dan URL
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|integer|exists:pohaci_conversations,id',
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'url' => 'nullable|url|max:2000',
            'disease_context' => 'nullable|string|max:200',
        ]);

        $conversation = $this->resolveConversation($request);
        $message = $request->input('message', '');
        $diseaseContext = $request->input('disease_context', 'Konsultasi Umum');
        $messageType = 'text';
        $userContent = $message;
        $messageMetadata = [
            'disease_context' => $diseaseContext,
        ];

        try {
            if ($request->hasFile('file')) {
                $messageType = 'vision';
                $file = $request->file('file');
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $mimeType = $file->getMimeType();

                if (empty($message)) {
                    $message = 'Analisa gambar ini dan jelaskan kondisi tanaman padi yang terlihat.';
                }

                $userContent = $message;
                $messageMetadata['mime_type'] = $mimeType;
                $messageMetadata['filename'] = $file->getClientOriginalName();
                $messageMetadata['has_file'] = true;
                $userMessage = $this->storeMessage($conversation, 'farmer', $userContent, true, $messageMetadata);
                $answer = $this->groq->chatWithImage($message, $base64, $mimeType);
                $aiMessage = $this->storeMessage($conversation, 'ai', $answer, false, [
                    'type' => $messageType,
                    'source_message_id' => $userMessage->id,
                ]);

                return response()->json([
                    'conversation_id' => $conversation->id,
                    'message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                    'answer' => $answer,
                    'model_used' => 'meta-llama/llama-4-scout-17b-16e-instruct',
                    'type' => $messageType,
                ]);
            }

            if ($request->filled('url')) {
                $messageType = 'url';
                $url = $request->input('url');

                if (empty($message)) {
                    $message = 'Rangkum dan analisa konten dari URL ini dalam konteks pertanian padi.';
                }

                $userContent = $message;
                $messageMetadata['url'] = $url;
                $messageMetadata['has_url'] = true;
                $userMessage = $this->storeMessage($conversation, 'farmer', $userContent, false, $messageMetadata);
                $answer = $this->groq->chatWithUrl($message, $url);
                $aiMessage = $this->storeMessage($conversation, 'ai', $answer, false, [
                    'type' => $messageType,
                    'source_message_id' => $userMessage->id,
                ]);

                return response()->json([
                    'conversation_id' => $conversation->id,
                    'message_id' => $userMessage->id,
                    'ai_message_id' => $aiMessage->id,
                    'answer' => $answer,
                    'model_used' => config('services.groq.default_model'),
                    'type' => $messageType,
                ]);
            }

            if (empty($message)) {
                return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
            }

            $messageMetadata['has_text'] = true;
            $userMessage = $this->storeMessage($conversation, 'farmer', $userContent, false, $messageMetadata);
            $answer = $this->groq->chat($message, null, $diseaseContext);
            $aiMessage = $this->storeMessage($conversation, 'ai', $answer, false, [
                'type' => $messageType,
                'source_message_id' => $userMessage->id,
            ]);

            return response()->json([
                'conversation_id' => $conversation->id,
                'message_id' => $userMessage->id,
                'ai_message_id' => $aiMessage->id,
                'answer' => $answer,
                'model_used' => config('services.groq.default_model'),
                'type' => $messageType,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses pesan: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function resolveConversation(Request $request): PohaciConversation
    {
        if ($request->filled('conversation_id')) {
            return PohaciConversation::findOrFail($request->input('conversation_id'));
        }

        return PohaciConversation::create([
            'user_id' => $request->user()?->id,
            'source' => $request->hasFile('file') ? 'image' : ($request->filled('url') ? 'url' : 'chat'),
            'status' => 'active',
            'metadata' => [
                'created_from' => 'chat',
            ],
        ]);
    }

    protected function storeMessage(PohaciConversation $conversation, string $senderType, string $content, bool $hasAttachment, array $metadata = []): PohaciMessage
    {
        return DB::transaction(function () use ($conversation, $senderType, $content, $hasAttachment, $metadata) {
            return PohaciMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'sender_type' => $senderType,
                'content' => $content,
                'has_attachment' => $hasAttachment,
                'metadata' => $metadata,
            ]);
        });
    }
}
