<?php

namespace Core;

class AvatarGenerator
{
    // Class properties to hold background color, text color, and avatar size
    private string $background;
    private string $color;
    private int $size;

    /**
     * Constructor to initialize avatar properties.
     * If no background is given, a random one will be generated.
     */
    public function __construct(string $background = '', string $color = 'fff', int $size = 128)
    {
        $this->background = $background ?: $this->generateRandomColor();
        $this->color = $color;
        $this->size = $size;
    }

    /**
     * Generates the avatar URL based on the provided name and instance properties.
     *
     * @param string $name - The name to display in the avatar.
     * @return string - The generated avatar URL.
     */
    public function generate(string $name): string
    {
        $encodedName = urlencode($name);
        return "https://ui-avatars.com/api/?name={$encodedName}&background={$this->background}&color={$this->color}&size={$this->size}";
    }

    /**
     * Generates a random hex color code.
     *
     * @return string - A randomly generated color in hexadecimal format.
     */
    private function generateRandomColor(): string
    {
        return sprintf('%02x%02x%02x', rand(0, 255), rand(0, 255), rand(0, 255));
    }

    /**
     * Parses an existing avatar URL and extracts its query parameters.
     *
     * @param string $url - The full avatar URL.
     * @return array - An associative array with 'name', 'background', and 'size'.
     */
    public function parseAvatarUrl(string $url): array
    {
        $parsedUrl = parse_url($url);                     // Parse the URL into components
        parse_str($parsedUrl['query'], $queryParams);     // Parse query string into array

        return [
            'name' => urldecode($queryParams['name'] ?? ''),
            'background' => $queryParams['background'] ?? '',
            'size' => isset($queryParams['size']) ? (int) $queryParams['size'] : 0
        ];
    }

    /**
     * Updates the avatar name while preserving the background color and size from an old URL.
     *
     * @param string $oldUrl - The existing avatar URL to extract background/size from.
     * @param string $newName - The new name to generate the updated avatar.
     * @return string - The new avatar URL with updated name and preserved settings.
     */
    public function updateNameKeepBackground(string $oldUrl, string $newName): string
    {
        $oldDetails = $this->parseAvatarUrl($oldUrl);  // Extract existing background and size

        // Preserve old background and size; fallback to random or default if missing
        $this->background = $oldDetails['background'] ?? $this->generateRandomColor();
        $this->size = $oldDetails['size'] ?? $this->size;

        return $this->generate($newName);  // Return new avatar URL with updated name
    }
}
