<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ImageController extends Controller
{
    #[OA\Post(
        path: '/api/imagenes',
        summary: 'Subir imagen',
        description: 'Sube una imagen al servidor y devuelve la URL pública. Requiere token JWT con rol admin. El nombre del archivo lo genera el servidor.',
        tags: ['Imágenes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['image'],
                properties: [
                    new OA\Property(
                        property: 'image',
                        type: 'string',
                        format: 'binary',
                        description: 'Archivo de imagen (jpeg, png, jpg, gif, webp; máx. 2 MB)'
                    ),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Imagen subida correctamente',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Imagen subida correctamente.'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'image', type: 'string', example: '/storage/images/Qx3b9kLmN0pZ.jpg'),
                    ]
                ),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'Prohibido. Solo administradores')]
    #[OA\Response(response: 422, description: 'Error de validación. Archivo inválido o grande')]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'image.required' => 'La imagen es requerida.',
            'image.image' => 'La imagen debe ser un archivo válido.',
            'image.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, gif, webp.',
            'image.max' => 'La imagen debe ser menor a 2MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // El nombre y la extensión los decide el servidor (a partir del contenido),
        // nunca el cliente: evita subir un "x.php" y colisiones de nombre.
        $image = $request->file('image');
        $ruta = $image->storeAs('images', $image->hashName(), 'public');

        $publicPath = Storage::url($ruta);

        return $this->successResponse(['image' => $publicPath], 'Imagen subida correctamente.');
    }
}
