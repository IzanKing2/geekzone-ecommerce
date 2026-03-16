<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'image.required' => 'La imagen es requerida.',
                'image.image' => 'La imagen debe ser un archivo válido.',
                'image.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, gif, svg.',
                'image.max' => 'La imagen debe ser menor a 2MB.',
            ]);
        
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
        
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            $ruta = $image->storeAs('images', $imageName, 'public');
        
            $publicPath = Storage::url($ruta);
        
        
            return $this->successResponse(
                ['image' => $publicPath],
                'Imagen subida correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al subir la imagen.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
