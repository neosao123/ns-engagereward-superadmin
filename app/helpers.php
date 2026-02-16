<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
//use Symfony\Component\Intl\Currencies;
use Akaunting\Money\Currency;
use Illuminate\Support\Facades\Crypt;

/*if (!function_exists('getCurrencyList')) {
	function getCurrencyList(){
		$currencies = [];

		foreach (Currencies::getCurrencyCodes() as $code) {
			try {
				$name = Currencies::getName($code);
				$symbol = Currencies::getSymbol($code);

				$currencies[$code] = "$code ($symbol)";
			} catch (\Exception $e) {
				continue;
			}
		}

		ksort($currencies); // Sort alphabetically
		return $currencies;
	}
}*/

if (!function_exists('getCurrencyList')) {
    function getCurrencyList()
    {
        $currencies = [];

        foreach (Currency::getCurrencies() as $code => $data) {
            try {
                $symbol = $data['symbol'] ?? '';
                $currencies[$code] = "$code ($symbol)";
            } catch (\Exception $e) {
                continue;
            }
        }

        ksort($currencies); // Sort alphabetically
        return $currencies;
    }


    if (!function_exists('getMailErrorMessage')) {

        function getMailErrorMessage(\Throwable $e): string
        {
            $error = strtolower($e->getMessage());

            //  Authentication / Credential issues
            if (
                str_contains($error, 'authentication') ||
                str_contains($error, 'auth failed') ||
                str_contains($error, 'username') ||
                str_contains($error, 'password')
            ) {
                return 'Please check your mail credentials.';
            }

            //  Relay / Permission issues
            if (
                str_contains($error, 'relaying denied') ||
                str_contains($error, 'not permitted') ||
                str_contains($error, '550')
            ) {
                return 'Mail server rejected the request. Please check mail configuration.';
            }

            //  Connection / Network issues
            if (
                str_contains($error, 'connection could not be established') ||
                str_contains($error, 'connection timed out') ||
                str_contains($error, 'could not connect') ||
                str_contains($error, 'getaddrinfo')
            ) {
                return 'Unable to connect to mail server. Please check mail host and port.';
            }

            //  Encryption / TLS / SSL issues
            if (
                str_contains($error, 'tls') ||
                str_contains($error, 'ssl') ||
                str_contains($error, 'encryption')
            ) {
                return 'Mail encryption configuration is incorrect.';
            }

            //  Sender / From address issues
            if (
                str_contains($error, 'from address') ||
                str_contains($error, 'sender address')
            ) {
                return 'Invalid sender email address configuration.';
            }

            //  Generic fallback
            return 'Mail sending failed. Please check your mail configuration.';
        }
    }
}


if (!function_exists('encryptData')) {
    function encryptData(string $data)
    {
        return Crypt::encryptString($data);
    }
}

if (!function_exists('decryptData')) {
    function decryptData(string $encryptedData)
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            return null; // Return null if decryption fails
        }
    }
}
