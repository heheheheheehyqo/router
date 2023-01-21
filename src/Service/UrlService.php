<?php

namespace Hyqo\Router\Service;

use Hyqo\Router\Exception\UrlBuilderException;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Route\Token;

class UrlService
{
    public function buildRouteUrl(Route $route, array $attributes = []): array|string|null
    {
        $tokens = $route->getTokens();

        $requiredTokens = [];

        $optionalValues = [];
        $optionalTokens = array_filter(
            $tokens,
            static function (Token $token) use (&$requiredTokens, &$optionalValues, $attributes) {
                if ($token->isOptional()) {
                    if ($value = $attributes[$token->getName()] ?? $token->getDefault()) {
                        $optionalValues[$token->getName()] = $value;
                    }

                    return true;
                }

                $requiredTokens[$token->getName()] = $token;

                return false;
            }
        );

        if ((0 !== $optionalValuesNum = count($optionalValues)) && $optionalValuesNum !== count($optionalTokens)) {
            $optionalValueNames = array_keys($optionalValues);
            $optionalTokenNames = array_keys($optionalTokens);

            foreach ($optionalValueNames as $i => $optionalValueName) {
                if ($optionalTokenNames[$i] !== $optionalValueName) {
                    $previousOptionalNames = array_slice($optionalTokenNames, 0, $i + 1);

                    throw new UrlBuilderException(
                        sprintf(
                            'Route "%s": previous optional attribute%s [%s] must be passed',
                            $route->getName(),
                            count($previousOptionalNames) > 1 ? 's' : '',
                            implode(', ', $previousOptionalNames)
                        )
                    );
                }
            }
        }

        if ($optionalValues) {
            foreach (array_reverse($optionalValues, true) as $name => $value) {
                $token = $tokens[$name];

                if ($token->getDefault() !== $value) {
                    break;
                }

                unset($optionalValues[$name]);
            }
        }

        $tokenValues = [];

        foreach ($requiredTokens as $token) {
            if (null === $value = $attributes[$token->getName()] ?? null) {
                throw new UrlBuilderException(
                    sprintf(
                        'Route "%s": attribute "%s" is required',
                        $route->getName(),
                        $token->getName()
                    )
                );
            }

            $tokenValues[$token->getName()] = $value;
        }

        $tokenValues = array_merge($tokenValues, $optionalValues);

        foreach ($tokenValues as $name => $tokenValue) {
            $token = $tokens[$name];

            if (!preg_match(sprintf('#^%s$#', $token->getRequirement()), $tokenValue)) {
                throw new UrlBuilderException(
                    sprintf(
                        'Route "%s": attribute "%s" must match the pattern "%s"',
                        $route->getName(),
                        $token->getName(),
                        $token->getRequirement()
                    )
                );
            }
        }

        return preg_replace_callback(
            '#(?P<char>.?){(?P<name>\w+)}#',
            static function (array $match) use ($tokenValues) {
                if (array_key_exists($match['name'], $tokenValues)) {
                    return $match['char'] . $tokenValues[$match['name']];
                }

                return '';
            },
            $route->getPattern()
        );
    }

}
