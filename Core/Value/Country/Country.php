<?php

namespace Amora\Core\Value;

use Amora\Core\Core;
use Throwable;

final class Country
{
    const COUNTRIES = [
        'AT' => ['id' => 'AT', 'name' => 'Austria'],
        'BE' => ['id' => 'BE', 'name' => 'Belgium'],
        'BG' => ['id' => 'BG', 'name' => 'Bulgaria'],
        'HR' => ['id' => 'HR', 'name' => 'Croatia'],
        'CY' => ['id' => 'CY', 'name' => 'Cyprus'],
        'CZ' => ['id' => 'CZ', 'name' => 'Czech Republic'],
        'DK' => ['id' => 'DK', 'name' => 'Denmark'],
        'EE' => ['id' => 'EE', 'name' => 'Estonia'],
        'FI' => ['id' => 'FI', 'name' => 'Finland'],
        'FR' => ['id' => 'FR', 'name' => 'France'],
        'DE' => ['id' => 'DE', 'name' => 'Germany'],
        'GR' => ['id' => 'GR', 'name' => 'Greece'],
        'HU' => ['id' => 'HU', 'name' => 'Hungary'],
        'IE' => ['id' => 'IE', 'name' => 'Ireland'],
        'IT' => ['id' => 'IT', 'name' => 'Italy'],
        'LV' => ['id' => 'LV', 'name' => 'Latvia'],
        'LT' => ['id' => 'LT', 'name' => 'Lithuania'],
        'LU' => ['id' => 'LU', 'name' => 'Luxembourg'],
        'MT' => ['id' => 'MT', 'name' => 'Malta'],
        'NL' => ['id' => 'NL', 'name' => 'Netherlands'],
        'PL' => ['id' => 'PL', 'name' => 'Poland'],
        'PT' => ['id' => 'PT', 'name' => 'Portugal'],
        'RO' => ['id' => 'RO', 'name' => 'Romania'],
        'SK' => ['id' => 'SK', 'name' => 'Slovakia'],
        'SI' => ['id' => 'SI', 'name' => 'Slovenia'],
        'ES' => ['id' => 'ES', 'name' => 'Spain'],
        'SE' => ['id' => 'SE', 'name' => 'Sweden'],
        'GB' => ['id' => 'GB', 'name' => 'United Kingdom'],

        'AX' => ['id' => 'AX', 'name' => 'Aland Islands'],
        'AF' => ['id' => 'AF', 'name' => 'Afghanistan'],
        'AL' => ['id' => 'AL', 'name' => 'Albania'],
        'DZ' => ['id' => 'DZ', 'name' => 'Algeria'],
        'AS' => ['id' => 'AS', 'name' => 'American Samoa'],
        'AD' => ['id' => 'AD', 'name' => 'Andorra'],
        'AO' => ['id' => 'AO', 'name' => 'Angola'],
        'AI' => ['id' => 'AI', 'name' => 'Anguilla'],
        'AG' => ['id' => 'AG', 'name' => 'Antigua and Barbuda'],
        'AR' => ['id' => 'AR', 'name' => 'Argentina'],
        'AM' => ['id' => 'AM', 'name' => 'Armenia'],
        'AW' => ['id' => 'AW', 'name' => 'Aruba'],
        'AU' => ['id' => 'AU', 'name' => 'Australia'],
        'AZ' => ['id' => 'AZ', 'name' => 'Azerbaijan'],
        'BS' => ['id' => 'BS', 'name' => 'Bahamas'],
        'BH' => ['id' => 'BH', 'name' => 'Bahrain'],
        'BD' => ['id' => 'BD', 'name' => 'Bangladesh'],
        'BB' => ['id' => 'BB', 'name' => 'Barbados'],
        'BY' => ['id' => 'BY', 'name' => 'Belarus'],
        'BZ' => ['id' => 'BZ', 'name' => 'Belize'],
        'BJ' => ['id' => 'BJ', 'name' => 'Benin'],
        'BM' => ['id' => 'BM', 'name' => 'Bermuda'],
        'BT' => ['id' => 'BT', 'name' => 'Bhutan'],
        'BO' => ['id' => 'BO', 'name' => 'Bolivia'],
        'BA' => ['id' => 'BA', 'name' => 'Bosnia and Herzegovina'],
        'BW' => ['id' => 'BW', 'name' => 'Botswana'],
        'BR' => ['id' => 'BR', 'name' => 'Brazil'],
        'VG' => ['id' => 'VG', 'name' => 'British Virgin Islands'],
        'BN' => ['id' => 'BN', 'name' => 'Brunei Darussalam'],
        'BF' => ['id' => 'BF', 'name' => 'Burkina Faso'],
        'BI' => ['id' => 'BI', 'name' => 'Burundi'],
        'KH' => ['id' => 'KH', 'name' => 'Cambodia'],
        'CM' => ['id' => 'CM', 'name' => 'Cameroon'],
        'CA' => ['id' => 'CA', 'name' => 'Canada'],
        'CV' => ['id' => 'CV', 'name' => 'Cape Verde'],
        'CW' => ['id' => 'CW', 'name' => 'Curacao'],
        'KY' => ['id' => 'KY', 'name' => 'Cayman Islands'],
        'CF' => ['id' => 'CF', 'name' => 'Central African Republic'],
        'TD' => ['id' => 'TD', 'name' => 'Chad'],
        'CL' => ['id' => 'CL', 'name' => 'Chile'],
        'CN' => ['id' => 'CN', 'name' => 'China'],
        'CX' => ['id' => 'CX', 'name' => 'Christmas Island'],
        'CO' => ['id' => 'CO', 'name' => 'Colombia'],
        'KM' => ['id' => 'KM', 'name' => 'Comoros'],
        'CG' => ['id' => 'CG', 'name' => 'Congo'],
        'CD' => ['id' => 'CD', 'name' => 'Congo, The Democratic Republic of The'],
        'CK' => ['id' => 'CK', 'name' => 'Cook Islands'],
        'CR' => ['id' => 'CR', 'name' => 'Costa Rica'],
        'CU' => ['id' => 'CU', 'name' => 'Cuba'],
        'DJ' => ['id' => 'DJ', 'name' => 'Djibouti'],
        'DM' => ['id' => 'DM', 'name' => 'Dominica'],
        'DO' => ['id' => 'DO', 'name' => 'Dominican Republic'],
        'EC' => ['id' => 'EC', 'name' => 'Ecuador'],
        'EG' => ['id' => 'EG', 'name' => 'Egypt'],
        'SV' => ['id' => 'SV', 'name' => 'El Salvador'],
        'GQ' => ['id' => 'GQ', 'name' => 'Equatorial Guinea'],
        'ER' => ['id' => 'ER', 'name' => 'Eritrea'],
        'ET' => ['id' => 'ET', 'name' => 'Ethiopia'],
        'FO' => ['id' => 'FO', 'name' => 'Faroe Islands'],
        'FJ' => ['id' => 'FJ', 'name' => 'Fiji'],
        'GF' => ['id' => 'GF', 'name' => 'French Guiana'],
        'PF' => ['id' => 'PF', 'name' => 'French Polynesia'],
        'TF' => ['id' => 'TF', 'name' => 'French Southern Territories'],
        'GA' => ['id' => 'GA', 'name' => 'Gabon'],
        'GM' => ['id' => 'GM', 'name' => 'Gambia'],
        'GE' => ['id' => 'GE', 'name' => 'Georgia'],
        'GH' => ['id' => 'GH', 'name' => 'Ghana'],
        'GI' => ['id' => 'GI', 'name' => 'Gibraltar'],
        'GL' => ['id' => 'GL', 'name' => 'Greenland'],
        'GD' => ['id' => 'GD', 'name' => 'Grenada'],
        'GP' => ['id' => 'GP', 'name' => 'Guadeloupe'],
        'GU' => ['id' => 'GU', 'name' => 'Guam'],
        'GT' => ['id' => 'GT', 'name' => 'Guatemala'],
        'GG' => ['id' => 'GG', 'name' => 'Guernsey'],
        'GN' => ['id' => 'GN', 'name' => 'Guinea'],
        'GY' => ['id' => 'GY', 'name' => 'Guyana'],
        'HT' => ['id' => 'HT', 'name' => 'Haiti'],
        'HN' => ['id' => 'HN', 'name' => 'Honduras'],
        'HK' => ['id' => 'HK', 'name' => 'Hong Kong'],
        'ID' => ['id' => 'ID', 'name' => 'Indonesia'],
        'IS' => ['id' => 'IS', 'name' => 'Iceland'],
        'IN' => ['id' => 'IN', 'name' => 'India'],
        'IR' => ['id' => 'IR', 'name' => 'Iran, Islamic Republic of'],
        'IQ' => ['id' => 'IQ', 'name' => 'Iraq'],
        'IM' => ['id' => 'IM', 'name' => 'Isle of Man'],
        'IL' => ['id' => 'IL', 'name' => 'Israel'],
        'CI' => ['id' => 'CI', 'name' => 'Ivory Coast'],
        'JM' => ['id' => 'JM', 'name' => 'Jamaica'],
        'JP' => ['id' => 'JP', 'name' => 'Japan'],
        'JE' => ['id' => 'JE', 'name' => 'Jersey'],
        'JO' => ['id' => 'JO', 'name' => 'Jordan'],
        'KZ' => ['id' => 'KZ', 'name' => 'Kazakhstan'],
        'KE' => ['id' => 'KE', 'name' => 'Kenya'],
        'KI' => ['id' => 'KI', 'name' => 'Kiribati'],
        'KP' => ['id' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of'],
        'KR' => ['id' => 'KR', 'name' => 'Korea, Republic of'],
        'KW' => ['id' => 'KW', 'name' => 'Kuwait'],
        'KG' => ['id' => 'KG', 'name' => 'Kyrgyzstan'],
        'LB' => ['id' => 'LB', 'name' => 'Lebanon'],
        'LS' => ['id' => 'LS', 'name' => 'Lesotho'],
        'LR' => ['id' => 'LR', 'name' => 'Liberia'],
        'LY' => ['id' => 'LY', 'name' => 'Libyan Arab Jamahiriya'],
        'LI' => ['id' => 'LI', 'name' => 'Liechtenstein'],
        'MO' => ['id' => 'MO', 'name' => 'Macao'],
        'MK' => ['id' => 'MK', 'name' => 'Macedonia'],
        'MG' => ['id' => 'MG', 'name' => 'Madagascar'],
        'MW' => ['id' => 'MW', 'name' => 'Malawi'],
        'MY' => ['id' => 'MY', 'name' => 'Malaysia'],
        'MV' => ['id' => 'MV', 'name' => 'Maldives'],
        'ML' => ['id' => 'ML', 'name' => 'Mali'],
        'MH' => ['id' => 'MH', 'name' => 'Marshall Islands'],
        'MQ' => ['id' => 'MQ', 'name' => 'Martinique'],
        'MR' => ['id' => 'MR', 'name' => 'Mauritania'],
        'MU' => ['id' => 'MU', 'name' => 'Mauritius'],
        'YT' => ['id' => 'YT', 'name' => 'Mayotte'],
        'MX' => ['id' => 'MX', 'name' => 'Mexico'],
        'FM' => ['id' => 'FM', 'name' => 'Micronesia, Federated States of'],
        'MD' => ['id' => 'MD', 'name' => 'Moldova, Republic of'],
        'MC' => ['id' => 'MC', 'name' => 'Monaco'],
        'MN' => ['id' => 'MN', 'name' => 'Mongolia'],
        'ME' => ['id' => 'ME', 'name' => 'Montenegro'],
        'MF' => ['id' => 'MF', 'name' => 'Collectivity of Saint Martin'],
        'MS' => ['id' => 'MS', 'name' => 'Montserrat'],
        'MA' => ['id' => 'MA', 'name' => 'Morocco'],
        'MZ' => ['id' => 'MZ', 'name' => 'Mozambique'],
        'MM' => ['id' => 'MM', 'name' => 'Myanmar'],
        'NA' => ['id' => 'NA', 'name' => 'Namibia'],
        'NR' => ['id' => 'NR', 'name' => 'Nauru'],
        'NP' => ['id' => 'NP', 'name' => 'Nepal'],
        'AN' => ['id' => 'AN', 'name' => 'Netherlands Antilles'],
        'NC' => ['id' => 'NC', 'name' => 'New Caledonia'],
        'NZ' => ['id' => 'NZ', 'name' => 'New Zealand'],
        'NI' => ['id' => 'NI', 'name' => 'Nicaragua'],
        'NE' => ['id' => 'NE', 'name' => 'Niger'],
        'NG' => ['id' => 'NG', 'name' => 'Nigeria'],
        'NU' => ['id' => 'NU', 'name' => 'Niue'],
        'NF' => ['id' => 'NF', 'name' => 'Norfolk Island'],
        'MP' => ['id' => 'MP', 'name' => 'Northern Mariana Islands'],
        'NO' => ['id' => 'NO', 'name' => 'Norway'],
        'OM' => ['id' => 'OM', 'name' => 'Oman'],
        'PK' => ['id' => 'PK', 'name' => 'Pakistan'],
        'PW' => ['id' => 'PW', 'name' => 'Palau'],
        'PS' => ['id' => 'PS', 'name' => 'Palestine'],
        'PA' => ['id' => 'PA', 'name' => 'Panama'],
        'PG' => ['id' => 'PG', 'name' => 'Papua New Guinea'],
        'PY' => ['id' => 'PY', 'name' => 'Paraguay'],
        'PE' => ['id' => 'PE', 'name' => 'Peru'],
        'PH' => ['id' => 'PH', 'name' => 'Philippines'],
        'PN' => ['id' => 'PN', 'name' => 'Pitcairn'],
        'PR' => ['id' => 'PR', 'name' => 'Puerto Rico'],
        'QA' => ['id' => 'QA', 'name' => 'Qatar'],
        'RE' => ['id' => 'RE', 'name' => 'Réunion'],
        'RU' => ['id' => 'RU', 'name' => 'Russia'],
        'RW' => ['id' => 'RW', 'name' => 'Rwanda'],
        'SH' => ['id' => 'SH', 'name' => 'Saint Helena'],
        'KN' => ['id' => 'KN', 'name' => 'Saint Kitts and Nevis'],
        'LC' => ['id' => 'LC', 'name' => 'Saint Lucia'],
        'PM' => ['id' => 'PM', 'name' => 'Saint Pierre and Miquelon'],
        'VC' => ['id' => 'VC', 'name' => 'Saint Vincent and The Grenadines'],
        'WS' => ['id' => 'WS', 'name' => 'Samoa'],
        'SM' => ['id' => 'SM', 'name' => 'San Marino'],
        'ST' => ['id' => 'ST', 'name' => 'Sao Tome and Principe'],
        'SA' => ['id' => 'SA', 'name' => 'Saudi Arabia'],
        'SN' => ['id' => 'SN', 'name' => 'Senegal'],
        'RS' => ['id' => 'RS', 'name' => 'Serbia'],
        'SC' => ['id' => 'SC', 'name' => 'Seychelles'],
        'SL' => ['id' => 'SL', 'name' => 'Sierra Leone'],
        'SG' => ['id' => 'SG', 'name' => 'Singapore'],
        'SB' => ['id' => 'SB', 'name' => 'Solomon Islands'],
        'SO' => ['id' => 'SO', 'name' => 'Somalia'],
        'ZA' => ['id' => 'ZA', 'name' => 'South Africa'],
        'LK' => ['id' => 'LK', 'name' => 'Sri Lanka'],
        'SD' => ['id' => 'SD', 'name' => 'Sudan'],
        'SR' => ['id' => 'SR', 'name' => 'Suriname'],
        'SJ' => ['id' => 'SJ', 'name' => 'Svalbard and Jan Mayen'],
        'SZ' => ['id' => 'SZ', 'name' => 'Swaziland'],
        'CH' => ['id' => 'CH', 'name' => 'Switzerland'],
        'SY' => ['id' => 'SY', 'name' => 'Syrian Arab Republic'],
        'TW' => ['id' => 'TW', 'name' => 'Taiwan, Province of China'],
        'TJ' => ['id' => 'TJ', 'name' => 'Tajikistan'],
        'TZ' => ['id' => 'TZ', 'name' => 'Tanzania, United Republic of'],
        'TH' => ['id' => 'TH', 'name' => 'Thailand'],
        'TG' => ['id' => 'TG', 'name' => 'Togo'],
        'TK' => ['id' => 'TK', 'name' => 'Tokelau'],
        'TO' => ['id' => 'TO', 'name' => 'Tonga'],
        'TT' => ['id' => 'TT', 'name' => 'Trinidad and Tobago'],
        'TN' => ['id' => 'TN', 'name' => 'Tunisia'],
        'TR' => ['id' => 'TR', 'name' => 'Turkey'],
        'TM' => ['id' => 'TM', 'name' => 'Turkmenistan'],
        'TC' => ['id' => 'TC', 'name' => 'Turks and Caicos Islands'],
        'TV' => ['id' => 'TV', 'name' => 'Tuvalu'],
        'UG' => ['id' => 'UG', 'name' => 'Uganda'],
        'UA' => ['id' => 'UA', 'name' => 'Ukraine'],
        'AE' => ['id' => 'AE', 'name' => 'United Arab Emirates'],
        'US' => ['id' => 'US', 'name' => 'United States'],
        'UM' => ['id' => 'UM', 'name' => 'United States Minor Outlying Islands'],
        'UY' => ['id' => 'UY', 'name' => 'Uruguay'],
        'UZ' => ['id' => 'UZ', 'name' => 'Uzbekistan'],
        'VU' => ['id' => 'VU', 'name' => 'Vanuatu'],
        'VA' => ['id' => 'VA', 'name' => 'Vatican City'],
        'VE' => ['id' => 'VE', 'name' => 'Venezuela'],
        'VN' => ['id' => 'VN', 'name' => 'Viet Nam'],
        'WF' => ['id' => 'WF', 'name' => 'Wallis and Futuna'],
        'EH' => ['id' => 'EH', 'name' => 'Western Sahara'],
        'YE' => ['id' => 'YE', 'name' => 'Yemen'],
        'ZM' => ['id' => 'ZM', 'name' => 'Zambia'],
        'ZW' => ['id' => 'ZW', 'name' => 'Zimbabwe']
    ];

    public static function getAll(): array
    {
        return array_values(self::COUNTRIES);
    }

    public static function getName(string $countryIsoCode): string
    {
        if (empty($countryIsoCode)) {
            return '';
        }

        if (strlen($countryIsoCode) !== 2) {
            try {
                Core::getDefaultLogger()->logError('Country ISO code not valid: ' . $countryIsoCode);
            } catch (Throwable) {}

            return '';
        }

        if (isset(self::COUNTRIES[$countryIsoCode])) {
            return self::COUNTRIES[$countryIsoCode]['name'];
        }

        try {
            Core::getDefaultLogger()->logError('Country ISO code not found: ' . $countryIsoCode);
        } catch (Throwable) {}

        return '';
    }
}

