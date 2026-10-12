<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Génère les icônes PWA depuis le logo de l'établissement.
 *
 * À rejouer après chaque changement de logo, puis `npm run build`
 * n'est PAS requis (fichiers statiques dans public/).
 */
class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:icons
        {--source=images/logo_roi.jpeg : Logo source, chemin relatif à public/}';

    protected $description = 'Génère les icônes PWA (192, 512, maskable, apple-touch, favicon) depuis le logo.';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('L’extension PHP GD est requise pour générer les icônes.');

            return self::FAILURE;
        }

        $source = public_path($this->option('source'));

        if (! is_file($source)) {
            $this->error("Logo introuvable : {$source}");

            return self::FAILURE;
        }

        $info = getimagesize($source);

        if ($info === false) {
            $this->error('Impossible de lire les dimensions du logo.');

            return self::FAILURE;
        }

        $loader = match ($info['mime']) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
            default => null,
        };

        if ($loader === null) {
            $this->error("Format de logo non pris en charge : {$info['mime']}");

            return self::FAILURE;
        }

        $logo = $loader($source);

        // Recadrage carré centré (les icônes PWA doivent être carrées).
        $side = min(imagesx($logo), imagesy($logo));
        $square = imagecreatetruecolor($side, $side);
        imagealphablending($square, false);
        imagesavealpha($square, true);
        imagecopy($square, $logo, 0, 0, (int) ((imagesx($logo) - $side) / 2), (int) ((imagesy($logo) - $side) / 2), $side, $side);
        imagedestroy($logo);

        $iconsDir = public_path('icons');

        if (! is_dir($iconsDir)) {
            mkdir($iconsDir, 0755, true);
        }

        // Icônes standards (fond blanc : rendu identique partout).
        $this->writeResized($square, "{$iconsDir}/icon-192x192.png", 192, [255, 255, 255]);
        $this->writeResized($square, "{$iconsDir}/icon-512x512.png", 512, [255, 255, 255]);
        $this->writeResized($square, "{$iconsDir}/apple-touch-icon-180x180.png", 180, [255, 255, 255]);
        $this->writeResized($square, "{$iconsDir}/favicon-32x32.png", 32, [255, 255, 255]);

        // Icône maskable : logo à 80 % sur fond couleur du thème (zone de sécurité).
        $this->writeMaskable($square, "{$iconsDir}/maskable-512x512.png", 512, [76, 29, 149]);

        // Vrai favicon.ico (le fichier actuel fait 0 octet) : conteneur ICO + PNG 32 px.
        $this->writeIco($square, public_path('favicon.ico'), 32);

        imagedestroy($square);

        $this->info('Icônes PWA générées dans public/icons + public/favicon.ico.');

        return self::SUCCESS;
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $background
     */
    private function writeResized(\GdImage $square, string $path, int $size, array $background): void
    {
        $side = imagesx($square);
        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...$background));
        imagecopyresampled($canvas, $square, 0, 0, 0, 0, $size, $size, $side, $side);
        imagepng($canvas, $path);
        imagedestroy($canvas);
        $this->line("  ✓ {$path}");
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $background
     */
    private function writeMaskable(\GdImage $square, string $path, int $size, array $background): void
    {
        $side = imagesx($square);
        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...$background));
        $margin = (int) ($size * 0.1);
        imagecopyresampled($canvas, $square, $margin, $margin, 0, 0, $size - 2 * $margin, $size - 2 * $margin, $side, $side);
        imagepng($canvas, $path);
        imagedestroy($canvas);
        $this->line("  ✓ {$path}");
    }

    private function writeIco(\GdImage $square, string $path, int $size): void
    {
        $side = imagesx($square);
        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $square, 0, 0, 0, 0, $size, $size, $side, $side);
        ob_start();
        imagepng($canvas);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        // Conteneur ICO (0, 1 image) + entrée pointant vers le PNG embarqué.
        $ico = pack('v3', 0, 1, 1).pack('C4v2V2', $size, $size, 0, 0, 1, 32, strlen($png), 6 + 16).$png;
        file_put_contents($path, $ico);
        $this->line("  ✓ {$path}");
    }
}
