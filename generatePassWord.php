<?php

class PasswordGenerator
{
    private const LOWER = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGITS = '0123456789';
    private const SYMBOLS = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    /**
     * Génère un mot de passe fort
     *
     * @param int $length Longueur du mot de passe
     * @param bool $includeUpper Inclure majuscules
     * @param bool $includeNumbers Inclure chiffres
     * @param bool $includeSymbols Inclure symboles
     * @return string Mot de passe généré
     */
    public static function generate(
        int $length = 16,
        bool $includeUpper = true,
        bool $includeNumbers = true,
        bool $includeSymbols = true
    ): string {
        // Validation de la longueur
        if ($length < 8) {
            throw new InvalidArgumentException("La longueur minimale est de 8 caractères");
        }

        // Construction du jeu de caractères
        $characters = self::LOWER;

        if ($includeUpper) {
            $characters .= self::UPPER;
        }

        if ($includeNumbers) {
            $characters .= self::DIGITS;
        }

        if ($includeSymbols) {
            $characters .= self::SYMBOLS;
        }

        // Vérification qu'on a au moins un type de caractère
        if (strlen($characters) === strlen(self::LOWER)) {
            throw new InvalidArgumentException("Au moins un type de caractère supplémentaire doit être activé");
        }

        // Utilisation de random_int pour la cryptographie
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }

        // Assurer la présence des types requis
        return self::ensureCharacterTypes($password, $includeUpper, $includeNumbers, $includeSymbols);
    }

    /**
     * Vérifie et garantit la présence des types de caractères requis
     */
    private static function ensureCharacterTypes(
        string $password,
        bool $includeUpper,
        bool $includeNumbers,
        bool $includeSymbols
    ): string {
        $passwordArray = str_split($password);

        // Vérifier les types présents
        $hasUpper = $includeUpper && preg_match('/[A-Z]/', $password);
        $hasNumber = $includeNumbers && preg_match('/[0-9]/', $password);
        $hasSymbol = $includeSymbols && preg_match('/[!@#$%^&*()\-_=+\[\]{}|;:,.<>?]/', $password);

        // Remplacer des caractères pour ajouter les types manquants
        $replacements = [];

        if ($includeUpper && !$hasUpper) {
            $replacements[] = ['pattern' => '/[a-z]/', 'characters' => self::UPPER];
        }

        if ($includeNumbers && !$hasNumber) {
            $replacements[] = ['pattern' => '/[a-z]/i', 'characters' => self::DIGITS];
        }

        if ($includeSymbols && !$hasSymbol) {
            $replacements[] = ['pattern' => '/[a-z0-9]/i', 'characters' => self::SYMBOLS];
        }

        // Appliquer les remplacements
        foreach ($replacements as $replacement) {
            $positions = [];
            for ($i = 0; $i < strlen($password); $i++) {
                if (preg_match($replacement['pattern'], $password[$i])) {
                    $positions[] = $i;
                }
            }

            if (!empty($positions)) {
                $pos = $positions[random_int(0, count($positions) - 1)];
                $passwordArray[$pos] = $replacement['characters'][random_int(0, strlen($replacement['characters']) - 1)];
            }
        }

        return implode('', $passwordArray);
    }

    /**
     * Évalue la force d'un mot de passe
     *
     * @param string $password Mot de passe à évaluer
     * @return array Score et recommandations
     */
    public static function evaluateStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longueur
        $length = strlen($password);
        if ($length >= 12) {
            $score += 3;
        } elseif ($length >= 8) {
            $score += 2;
        } elseif ($length >= 6) {
            $score += 1;
        } else {
            $feedback[] = 'Mot de passe trop court (minimum 8 caractères recommandés)';
        }

        // Diversité des caractères
        if (preg_match('/[a-z]/', $password)) $score += 1;
        if (preg_match('/[A-Z]/', $password)) $score += 1;
        if (preg_match('/[0-9]/', $password)) $score += 1;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 2;

        // Vérifier les motifs faibles
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 2;
            $feedback[] = 'Évitez les répétitions de caractères';
        }

        if (preg_match('/^(1234|abcd|password|admin|qwerty)/i', $password)) {
            $score -= 3;
            $feedback[] = 'Évitez les séquences courantes';
        }

        // Évaluation finale
        $strength = 'Faible';
        if ($score >= 8) {
            $strength = 'Très fort';
        } elseif ($score >= 6) {
            $strength = 'Fort';
        } elseif ($score >= 4) {
            $strength = 'Moyen';
        }

        return [
            'score' => $score,
            'strength' => $strength,
            'feedback' => $feedback,
            'length' => $length,
            'has_lowercase' => (bool)preg_match('/[a-z]/', $password),
            'has_uppercase' => (bool)preg_match('/[A-Z]/', $password),
            'has_digits' => (bool)preg_match('/[0-9]/', $password),
            'has_symbols' => (bool)preg_match('/[^a-zA-Z0-9]/', $password),
        ];
    }

    /**
     * Génère un mot de passe mémorisable
     */
    public static function generateMemorable(int $wordCount = 4, string $separator = '-'): string
    {
        $words = [
            'chaise', 'table', 'livre', 'soleil', 'lune', 'etoile', 'montagne', 'riviere',
            'ocean', 'foret', 'avion', 'train', 'voiture', 'bateau', 'ville', 'campagne',
            'musique', 'couleur', 'animal', 'plante', 'fruit', 'legume', 'saison', 'moment',
            'secret', 'histoire', 'voyage', 'reves', 'idees', 'projet', 'systeme', 'message'
        ];

        $passwordWords = [];
        $max = count($words) - 1;

        for ($i = 0; $i < $wordCount; $i++) {
            $passwordWords[] = $words[random_int(0, $max)];
        }

        $password = implode($separator, $passwordWords);

        // Ajouter un nombre pour plus de sécurité
        $password .= random_int(10, 99);

        return $password;
    }
}

// Exemple 1: Générer un mot de passe standard
$password1 = PasswordGenerator::generate();
echo "Mot de passe standard: " . $password1 . "\n\n";
