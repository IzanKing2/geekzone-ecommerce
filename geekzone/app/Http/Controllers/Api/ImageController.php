<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ImageController extends Controller
{
    #[OA\Post(
        path: "/api/imagenes",
        summary: "Subir imagen",
        description: "Sube una imagen al servidor y devuelve la URL pública. Requiere token JWT.",
        tags: ["Imágenes"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["image"],
                properties: [
                    new OA\Property(
                        property: "image",
                        type: "string",
                        format: "binary",
                        description: "Archivo de imagen (jpeg, png, jpg, gif, svg)"
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Imagen subida correctamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Imagen subida correctamente."),
                new OA\Property(property: "image", type: "string", example: "/storage/images/1713100000.jpg")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 422, description: "Error de validación. Archivo inválido o grande")]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'image.required' => 'La imagen es requerida.',
            'image.image' => 'La imagen debe ser un archivo válido.',
            'image.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, gif, svg.',
            'image.max' => 'La imagen debe ser menor a 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();

        $ruta = $image->storeAs('images', $imageName, 'public');

        $publicPath = Storage::url($ruta);


        return response()->json([
            'message' => 'Imagen subida correctamente.',
            'image' => $publicPath,
        ], 200);
    }
}
