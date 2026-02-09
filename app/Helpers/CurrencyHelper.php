<?php

if (!function_exists('formatIndianCurrency')) {
    /**
     * Format number to Indian Currency format (e.g., 1,00,000.00)
     *
     * @param float|int|string $number
     * @return string
     */
    function formatIndianCurrency($number)
    {
        $decimal = '';
        if (strpos((string) $number, '.') !== false) {
            $list = explode('.', (string) $number);
            $number = $list[0];
            $decimal = isset($list[1]) ? substr($list[1], 0, 2) : '00';
            if (strlen($decimal) == 1) {
                $decimal .= '0';
            }
            $decimal = '.' . $decimal;
        } else {
            $decimal = '.00';
        }

        $number = (string) $number;
        $last3 = substr($number, -3);
        $remaining = substr($number, 0, -3);

        if ($remaining != '') {
            $last3 = ',' . $last3;
        }

        $formatted = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $remaining) . $last3 . $decimal;

        return $formatted;
    }
}
