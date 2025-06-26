<?php

namespace AlexandreChollet\PhpCsFixerConstructorMultiline;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ConstructorMultilineFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Constructor arguments should be on separate lines if there are multiple arguments.',
            [
                new CodeSample(
                    '<?php
class Test {
    public function __construct($arg1, $arg2, $arg3) {
        // ...
    }
}'
                ),
                new CodeSample(
                    '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3
    ) {
        // ...
    }
}'
                ),
            ]
        );
    }

    public function getName(): string
    {
        return 'YourNamespace/constructor_multiline';
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    public function isRisky(): bool
    {
        return false;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(T_FUNCTION)) {
                continue;
            }

            // Find the function name token
            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            if (null === $functionNameIndex || strtolower($tokens[$functionNameIndex]->getContent()) !== '__construct') {
                continue;
            }

            $argumentsStart = $tokens->getNextTokenOfKind($index, ['(']);
            if (null === $argumentsStart) {
                continue;
            }

            $argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $argumentsStart);
            if (null === $argumentsEnd) {
                continue;
            }

            // Count arguments
            $arguments = $argumentsAnalyzer->getArguments($tokens, $argumentsStart, $argumentsEnd);
            if (count($arguments) <= 1) {
                continue;
            }

            // Break into multiline
            $this->breakIntoMultiline($tokens, $argumentsStart, $argumentsEnd, $arguments);
        }
    }

    private function breakIntoMultiline(Tokens $tokens, int $start, int $end, array $arguments): void
    {
        // Collect argument content before clearing and clean it up
        $argumentContents = [];
        $argumentKeys = array_keys($arguments);
        $argumentValues = array_values($arguments);
        
        for ($i = 0; $i < count($arguments); $i++) {
            $argumentStart = $argumentKeys[$i];
            $argumentEnd = $argumentValues[$i];
            
            // Check if next argument starts with a comment
            if ($i + 1 < count($arguments)) {
                $nextArgumentStart = $argumentKeys[$i + 1];
                $nextArgumentEnd = $argumentValues[$i + 1];
                
                // Look for comment at the beginning of next argument
                $nextContent = $tokens->generatePartialCode($nextArgumentStart, $nextArgumentEnd);
                if (preg_match('/^\s*\/\/[^\n]*/', $nextContent, $matches)) {
                    // Find where the comment ends (before the next argument starts)
                    $commentEnd = $nextArgumentStart;
                    for ($j = $nextArgumentStart; $j <= $nextArgumentEnd; $j++) {
                        if ($tokens[$j]->isGivenKind(T_COMMENT)) {
                            $commentEnd = $j;
                            break;
                        }
                    }
                    // Extend current argument to include the comment, but NOT the comma
                    $argumentEnd = $commentEnd;
                    // Find the next argument start (skip comment, whitespace, and comma)
                    $nextStart = $commentEnd + 1;
                    while ($nextStart <= $nextArgumentEnd && ($tokens[$nextStart]->isWhitespace() || $tokens[$nextStart]->equals(','))) {
                        $nextStart++;
                    }
                    $argumentKeys[$i + 1] = $nextStart;
                }
            }

            $content = $tokens->generatePartialCode($argumentStart, $argumentEnd);
            // Clean up whitespace and remove any trailing comma
            $cleanContent = preg_replace('/\s+/', ' ', trim($content));
            $cleanContent = preg_replace('/,\s*$/', '', $cleanContent);
            
            $argumentContents[] = $cleanContent;
        }

        // Remove existing content
        $tokens->clearRange($start + 1, $end - 1);

        // Add opening parenthesis and newline with 8 spaces
        $tokens->insertAt($start + 1, new Token([T_WHITESPACE, "\n        "]));

        // Add arguments
        $currentIndex = $start + 2;
        foreach ($argumentContents as $index => $argumentContent) {
            if ($index > 0) {
                // Only add comma if the previous argument doesn't end with a comment
                if (!preg_match('/\/\/[^\/]*$/', $argumentContents[$index - 1])) {
                    $tokens->insertAt($currentIndex, new Token(','));
                    $currentIndex++;
                }
                $tokens->insertAt($currentIndex, new Token([T_WHITESPACE, "\n        "]));
                $currentIndex++;
            }

            // Create tokens from the cleaned argument content
            $argumentTokens = Tokens::fromCode($argumentContent);
            
            foreach ($argumentTokens as $token) {
                $tokens->insertAt($currentIndex, $token);
                $currentIndex++;
            }
        }

        // Add closing parenthesis and newline with proper indentation
        $tokens->insertAt($currentIndex, new Token([T_WHITESPACE, "\n    "]));
    }
}