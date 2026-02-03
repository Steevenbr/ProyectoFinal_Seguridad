<?php

namespace App\Services;

use App\Models\AuditBlock;
use Illuminate\Support\Facades\DB;

class AuditChain
{
    private function sortRecursive(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->sortRecursive($v);
            }
        }
        ksort($data);
        return $data;
    }

    private function makeHash(array $payload): string
    {
        $payload = $this->sortRecursive($payload);

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return hash('sha256', $json);
    }

    public function add(
        ?int $userId,
        string $eventType,
        array $eventData,
        ?string $ip = null,
        ?string $userAgent = null
    ): AuditBlock {
        return DB::transaction(function () use ($userId, $eventType, $eventData, $ip, $userAgent) {

            // Evita que 2 requests creen el mismo sequence
            $last = AuditBlock::orderByDesc('sequence')->lockForUpdate()->first();

            $sequence = $last ? ($last->sequence + 1) : 1;
            $prevHash = $last ? $last->hash : str_repeat('0', 64);

            $ua = $userAgent ? mb_substr($userAgent, 0, 255) : null;
            $createdAt = now()->toISOString();

            $payload = [
                'sequence'   => $sequence,
                'user_id'    => $userId,
                'event_type' => $eventType,
                'event_data' => $eventData,
                'ip'         => $ip,
                'user_agent' => $ua,
                'prev_hash'  => $prevHash,
                'created_at' => $createdAt,
            ];

            $hash = $this->makeHash($payload);

            return AuditBlock::create([
                'sequence'   => $sequence,
                'user_id'    => $userId,
                'event_type' => $eventType,
                'event_data' => $eventData,
                'ip'         => $ip,
                'user_agent' => $ua,
                'prev_hash'  => $prevHash,
                'hash'       => $hash,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        });
    }

    public function verify(): array
    {
        $blocks = AuditBlock::orderBy('sequence')->get();

        $prevHash = str_repeat('0', 64);

        foreach ($blocks as $block) {
            $payload = [
                'sequence'   => (int) $block->sequence,
                'user_id'    => $block->user_id,
                'event_type' => $block->event_type,
                'event_data' => $block->event_data,
                'ip'         => $block->ip,
                'user_agent' => $block->user_agent,
                'prev_hash'  => $prevHash,
                'created_at' => optional($block->created_at)->toISOString(),
            ];

            $expected = $this->makeHash($payload);

            if ($block->prev_hash !== $prevHash) {
                return [
                    'ok' => false,
                    'broken_at' => (int) $block->sequence,
                    'reason' => 'prev_hash_no_coincide',
                ];
            }

            if ($block->hash !== $expected) {
                return [
                    'ok' => false,
                    'broken_at' => (int) $block->sequence,
                    'reason' => 'hash_no_coincide',
                    'expected' => $expected,
                    'found' => $block->hash,
                ];
            }

            $prevHash = $block->hash;
        }

        return ['ok' => true, 'broken_at' => null];
    }
}
