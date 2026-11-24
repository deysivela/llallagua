<?php

namespace App\Salud;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManagerStatic as Image;

class SaludArchivoManager
{
    const MAX_BYTES = 10485760; // 10 MB

    const MIMES_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'application/pdf' => 'pdf',
    ];

    /**
     * Guarda un archivo en el disco public (public/server/salud), genera
     * miniatura y versión optimizada cuando es una imagen y devuelve el
     * registro SaludArchivo creado.
     *
     * @throws ValidationException
     */
    public function guardar(UploadedFile $file, $usuarioId = null): SaludArchivo
    {
        if (!$file->isValid()) {
            $this->error('El archivo no se pudo subir correctamente.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            $this->error('El archivo no debe superar los 10 MB.');
        }

        $mime = $file->getMimeType();

        if (!isset(self::MIMES_PERMITIDOS[$mime])) {
            $this->error('Formato no permitido. Use JPG, PNG, WEBP, GIF o PDF.');
        }

        $extension = self::MIMES_PERMITIDOS[$mime];
        $nombre    = Str::random(40) . '.' . $extension;
        $carpeta   = 'server/salud/archivos';
        $clave     = $carpeta . '/' . $nombre;

        $ancho = null;
        $alto  = null;
        $claveThumb = null;
        $claveOpt   = null;

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            $resultado = $this->guardarImagen($file, $nombre, $mime);

            $clave      = $resultado['clave'];
            $claveThumb = $resultado['clave_thumb'];
            $claveOpt   = $resultado['clave_opt'];
            $ancho      = $resultado['ancho'];
            $alto       = $resultado['alto'];
        } else {
            Storage::disk('public')->put($clave, file_get_contents($file->getRealPath()));
        }

        return SaludArchivo::create([
            'clave'           => $clave,
            'clave_thumb'     => $claveThumb,
            'clave_opt'       => $claveOpt,
            'nombre_original' => $file->getClientOriginalName(),
            'mime'            => $mime,
            'tamano'          => $file->getSize(),
            'ancho'           => $ancho,
            'alto'            => $alto,
            'subido_por'      => $usuarioId,
        ]);
    }

    /**
     * Re-encodifica la imagen para quitar EXIF, guarda el original, la
     * miniatura (280 px) y la versión optimizada (máx. 1600 px).
     * Si el servidor no tiene GD se guarda el archivo tal cual.
     */
    protected function guardarImagen(UploadedFile $file, $nombre, $mime): array
    {
        $extension = self::MIMES_PERMITIDOS[$mime];
        $clave     = 'server/salud/archivos/' . $nombre;
        $nombreBase = pathinfo($nombre, PATHINFO_FILENAME);

        $resultado = [
            'clave'       => $clave,
            'clave_thumb' => null,
            'clave_opt'   => null,
            'ancho'       => null,
            'alto'        => null,
        ];

        try {
            if (!extension_loaded('gd')) {
                throw new \RuntimeException('La extensión GD no está disponible.');
            }

            $original = Image::make($file->getRealPath())->orientate();

            $resultado['ancho'] = $original->width();
            $resultado['alto']  = $original->height();

            // Guardar original Ya sin metadatos EXIF.
            Storage::disk('public')->put($clave, (string) $original->encode($extension, 90));

            $thumbClave = 'server/salud/thumbs/' . $nombreBase . '-280.' . $extension;
            $thumb = Image::make($file->getRealPath())
                ->orientate()
                ->resize(280, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            Storage::disk('public')->put($thumbClave, (string) $thumb->encode($extension, 85));
            $resultado['clave_thumb'] = $thumbClave;

            $optClave = 'server/salud/archivos/' . $nombreBase . '-1600.' . $extension;
            $opt = Image::make($file->getRealPath())
                ->orientate()
                ->resize(1600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            Storage::disk('public')->put($optClave, (string) $opt->encode($extension, 88));
            $resultado['clave_opt'] = $optClave;
        } catch (\Exception $e) {
            Log::warning('Salud: no se pudo procesar la imagen, se guarda el original.', [
                'error' => $e->getMessage(),
            ]);

            $resultado['clave']       = $clave;
            $resultado['clave_thumb'] = null;
            $resultado['clave_opt']   = null;

            Storage::disk('public')->put($clave, file_get_contents($file->getRealPath()));

            $medidas = @getimagesize($file->getRealPath());
            if ($medidas) {
                $resultado['ancho'] = $medidas[0];
                $resultado['alto']  = $medidas[1];
            }
        }

        return $resultado;
    }

    /**
     * Elimina los archivos físicos de un registro.
     */
    public function eliminar(SaludArchivo $archivo)
    {
        foreach ([$archivo->clave, $archivo->clave_thumb, $archivo->clave_opt] as $clave) {
            if ($clave && Storage::disk('public')->exists($clave)) {
                Storage::disk('public')->delete($clave);
            }
        }

        $archivo->delete();
    }

    /**
     * @throws ValidationException
     */
    protected function error($mensaje)
    {
        throw ValidationException::withMessages([
            'archivo' => [$mensaje],
        ]);
    }
}
