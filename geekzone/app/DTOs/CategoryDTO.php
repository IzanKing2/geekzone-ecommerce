<?php

namespace App\DTOs;

/**
 * DTO para la entidad Categoría.
 * Mapea manualmente los resultados del CRUD SQL puro del CategoryController.
 */
class CategoryDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly ?string $image_url,
        public readonly string  $created_at,
        public readonly string  $updated_at,
        public readonly int     $products_count = 0,
    ) {}

    /**
     * Construye un DTO a partir de una fila devuelta por DB::select().
     */
    public static function fromRow(object $row): self
    {
        return new self(
            id:             (int) $row->id,
            name:           $row->name,
            description:    $row->description  ?? null,
            image_url:      $row->image_url    ?? null,
            created_at:     $row->created_at,
            updated_at:     $row->updated_at,
            products_count: isset($row->products_count) ? (int) $row->products_count : 0,
        );
    }

    /**
     * Serializa el DTO a array para la respuesta JSON.
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'image_url'      => $this->image_url,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'products_count' => $this->products_count,
        ];
    }
}
