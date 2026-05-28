<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'key';
    protected $returnType    = 'array';
    protected $allowedFields = ['key', 'value', 'description', 'updated_at'];
    protected $useTimestamps = false;

    private static array $cache = [];

    /**
     * Lay gia tri cai dat theo key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $row = $this->find($key);
            $value = $row ? $row['value'] : $default;
            self::$cache[$key] = $value;
            return $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Luu gia tri cai dat
     */
    public function saveSetting(string $key, mixed $value, string $description = ''): bool
    {
        self::$cache[$key] = $value;

        $existing = $this->find($key);
        $data = [
            'key'        => $key,
            'value'      => (string) $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($description) {
            $data['description'] = $description;
        }

        if ($existing) {
            return $this->update($key, $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Lay nhieu cai dat cung luc
     */
    public function getMany(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Luu nhieu cai dat cung luc
     */
    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->saveSetting($key, $value);
        }
    }

    /**
     * Lay tat ca cai dat (cho admin panel)
     */
    public function getAllSettings(): array
    {
        try {
            $rows = $this->findAll();
            $result = [];
            foreach ($rows as $row) {
                $result[$row['key']] = $row['value'];
            }
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
