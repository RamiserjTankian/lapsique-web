<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ImageCompressionService
{
    /**
     * Compress and save an uploaded image
     * 
     * @param UploadedFile|TemporaryUploadedFile $file
     * @param string $disk
     * @param string $directory
     * @return string|null The path to the saved file
     */
    public function compressAndSave($file, string $disk = 'public', string $directory = 'campaigns/attachments'): ?string
    {
        try {
            // Validar que sea una imagen
            $mimeType = $file instanceof TemporaryUploadedFile 
                ? $file->getMimeType() 
                : $file->getMimeType();
                
            if (!str_starts_with($mimeType, 'image/')) {
                return null;
            }

            // Generar nombre único para el archivo
            $extension = $file instanceof TemporaryUploadedFile
                ? $file->getClientOriginalExtension()
                : $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $directory . '/' . $filename;

            // Primero guardar el archivo sin comprimir
            if ($file instanceof TemporaryUploadedFile) {
                $savedPath = $file->storePubliclyAs($directory, $filename, $disk);
            } else {
                $savedPath = $file->storeAs($directory, $filename, $disk);
            }

            if (!$savedPath) {
                Log::error('Failed to save file');
                return null;
            }

            // Obtener la ruta completa del archivo guardado
            $fullPath = Storage::disk($disk)->path($savedPath);

            if (!file_exists($fullPath)) {
                Log::error('Saved file does not exist: ' . $fullPath);
                return $savedPath; // Devolver la ruta aunque no exista físicamente
            }

            // Crear una copia temporal en un directorio temporal (no modificar el original directamente)
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFilename = 'optimize_' . uniqid() . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);
            $optimizePath = $tempDir . '/' . $tempFilename;
            copy($fullPath, $optimizePath);

            // SIEMPRE redimensionar a máximo 600px de ancho para emails
            // Esto es crítico para que las imágenes se vean bien en Gmail y otros clientes de correo
            $this->resizeIfNeeded($optimizePath, 600);
            
            // Verificar y forzar redimensionamiento si es necesario (doble verificación)
            if (file_exists($optimizePath)) {
                try {
                    $checkImage = \Spatie\Image\Image::load($optimizePath);
                    $finalWidth = $checkImage->getWidth();
                    $finalHeight = $checkImage->getHeight();
                    
                    if ($finalWidth > 600) {
                        Log::warning("Image still too large after resize: {$finalWidth}px. Forcing resize to 600px.");
                        // Forzar redimensionamiento de forma más agresiva
                        $checkImage->width(600)->save();
                        
                        // Verificar una vez más
                        $verifyImage = \Spatie\Image\Image::load($optimizePath);
                        $verifyWidth = $verifyImage->getWidth();
                        Log::info("After forced resize: {$verifyWidth}px width");
                    } else {
                        Log::info("✓ Image correctly resized to {$finalWidth}x{$finalHeight}");
                    }
                } catch (\Exception $e) {
                    Log::error('Could not verify/resize image: ' . $e->getMessage());
                    // Intentar redimensionar directamente sin verificar
                    try {
                        $forceImage = \Spatie\Image\Image::load($optimizePath);
                        $forceImage->width(600)->save();
                        Log::info("Forced resize completed");
                    } catch (\Exception $e2) {
                        Log::error('Failed to force resize: ' . $e2->getMessage());
                    }
                }
            }

            // Optimizar la imagen después de redimensionar
            try {
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize($optimizePath);
            } catch (\Exception $e) {
                Log::warning('Could not optimize image: ' . $e->getMessage());
                // Continuar sin optimizar
            }

            // Reemplazar el archivo original con el optimizado
            if (file_exists($optimizePath)) {
                copy($optimizePath, $fullPath);
                @unlink($optimizePath); // Limpiar archivo temporal
            }

            return $savedPath;
        } catch (\Exception $e) {
            Log::error('Error compressing image: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            // Si falla la compresión, intentar guardar sin comprimir
            try {
                if ($file instanceof TemporaryUploadedFile) {
                    return $file->storePubliclyAs($directory, $filename ?? time() . '_' . uniqid() . '.' . $extension, $disk);
                } else {
                    return $file->storeAs($directory, $filename ?? time() . '_' . uniqid() . '.' . $extension, $disk);
                }
            } catch (\Exception $e2) {
                Log::error('Error saving original image: ' . $e2->getMessage());
                return null;
            }
        }
    }

    /**
     * Save file without compression as fallback
     */
    protected function saveWithoutCompression($file, string $disk, string $directory, ?string $filename = null): ?string
    {
        try {
            if (!$filename) {
                $extension = $file instanceof TemporaryUploadedFile
                    ? $file->getClientOriginalExtension()
                    : $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
            }
            
            if ($file instanceof TemporaryUploadedFile) {
                // Para TemporaryUploadedFile, usar storePubliclyAs
                return $file->storePubliclyAs($directory, $filename, $disk);
            } else {
                return $file->storeAs($directory, $filename, $disk);
            }
        } catch (\Exception $e) {
            Log::error('Error saving original image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resize image if it's too large
     * Mantiene el aspect ratio automáticamente usando width()
     * SIEMPRE redimensiona a máximo 600px para emails
     */
    protected function resizeIfNeeded(string $imagePath, int $maxWidth): void
    {
        try {
            if (!file_exists($imagePath)) {
                Log::error('Image file does not exist for resize: ' . $imagePath);
                return;
            }
            
            $image = \Spatie\Image\Image::load($imagePath);
            
            $currentWidth = $image->getWidth();
            $currentHeight = $image->getHeight();
            
            Log::info("Resizing image: {$currentWidth}x{$currentHeight} -> max {$maxWidth}px");
            
            // SIEMPRE redimensionar si es más grande que el máximo (para emails)
            if ($currentWidth > $maxWidth) {
                // Calcular altura proporcional
                $ratio = $currentHeight / $currentWidth;
                $newHeight = (int) ($maxWidth * $ratio);
                
                // Usar width() que automáticamente mantiene el aspect ratio
                // También especificar calidad para reducir tamaño de archivo
                $image->width($maxWidth)
                      ->quality(85) // Calidad optimizada para emails
                      ->format('jpg') // Convertir a JPG para mejor compresión
                      ->save();
                      
                // Verificar las dimensiones finales
                $finalImage = \Spatie\Image\Image::load($imagePath);
                $finalWidth = $finalImage->getWidth();
                $finalHeight = $finalImage->getHeight();
                
                // Obtener tamaño del archivo
                $fileSize = filesize($imagePath);
                $fileSizeMB = round($fileSize / 1024 / 1024, 2);
                      
                Log::info("✓ Image resized from {$currentWidth}x{$currentHeight} to {$finalWidth}x{$finalHeight} ({$fileSizeMB}MB)");
            } else {
                Log::info("Image already within size limit: {$currentWidth}px <= {$maxWidth}px");
            }
        } catch (\Exception $e) {
            Log::error('Could not resize image: ' . $e->getMessage() . ' | Path: ' . $imagePath);
            // Intentar método alternativo si falla
            try {
                // Método alternativo usando GD directamente
                $this->resizeWithGD($imagePath, $maxWidth);
            } catch (\Exception $e2) {
                Log::error('Alternative resize also failed: ' . $e2->getMessage());
            }
        }
    }
    
    /**
     * Método alternativo de redimensionamiento usando GD
     */
    protected function resizeWithGD(string $imagePath, int $maxWidth): void
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return;
        }
        
        list($currentWidth, $currentHeight, $type) = $imageInfo;
        
        if ($currentWidth <= $maxWidth) {
            return;
        }
        
        $ratio = $currentHeight / $currentWidth;
        $newHeight = (int) ($maxWidth * $ratio);
        
        // Crear imagen según el tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($imagePath);
                break;
            default:
                return;
        }
        
        if (!$source) {
            return;
        }
        
        // Crear nueva imagen redimensionada
        $destination = imagecreatetruecolor($maxWidth, $newHeight);
        
        // Mantener transparencia para PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }
        
        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $currentWidth, $currentHeight);
        
        // Guardar según el tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $imagePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $imagePath, 8);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destination, $imagePath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($destination);
        
        Log::info("Image resized using GD from {$currentWidth}x{$currentHeight} to {$maxWidth}x{$newHeight}");
    }

    /**
     * Get the public URL for a stored image
     */
    public function getPublicUrl(string $path, string $disk = 'public'): string
    {
        // Construir URL directamente basándose en la configuración del disco
        $baseUrl = config('app.url');
        $relativePath = '/storage/' . ltrim($path, '/');
        $url = $baseUrl . $relativePath;
        
        return $url;
    }
}

