<?php

namespace PommProject\SymfonyBridge\Twig;

final class Extension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new \Twig\TwigFunction('color', array($this, 'color')),
        );
    }

    /*
     * https://stackoverflow.com/questions/2353211/hsl-to-rgb-color-conversion
     */
    public function color($percent)
    {
        $hue = (100 - $percent) * 1.2 / 360;
        $rgb = $this->hslToRgb($hue, .9, .4);
        return sprintf("rgb(%d, %d, %d)", $rgb[0], $rgb[1], $rgb[2]);
    }

    private function hslToRgb($h, $s, $l)
    {
        $r = $g = $b = $l;

        if ($s !== 0) {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hue2rgb($p, $q, $h + 1 / 3);
            $g = $this->hue2rgb($p, $q, $h);
            $b = $this->hue2rgb($p, $q, $h - 1 / 3);
        }

        return array($r * 255, $g * 255, $b * 255);
    }

    private function hue2rgb($p, $q, $t)
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }
}
