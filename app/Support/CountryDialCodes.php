<?php

namespace App\Support;

class CountryDialCodes
{
    public const DEFAULT_ISO = 'SA';

    /** @return list<array{iso: string, dial: string, flag: string, name_ar: string, name_en: string}> */
    public static function all(): array
    {
        $rows = [];

        foreach (self::catalog() as $iso => $row) {
            $rows[] = [
                'iso' => $iso,
                'dial' => $row[0],
                'flag' => self::flag($iso),
                'name_ar' => $row[1],
                'name_en' => $row[2],
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($a['name_ar'], $b['name_ar']));

        return $rows;
    }

    /** @return array{iso: string, dial: string, flag: string, name_ar: string, name_en: string}|null */
    public static function find(string $iso): ?array
    {
        $iso = strtoupper($iso);
        $row = self::catalog()[$iso] ?? null;

        if (! $row) {
            return null;
        }

        return [
            'iso' => $iso,
            'dial' => $row[0],
            'flag' => self::flag($iso),
            'name_ar' => $row[1],
            'name_en' => $row[2],
        ];
    }

    public static function has(string $iso): bool
    {
        return isset(self::catalog()[strtoupper($iso)]);
    }

    public static function dialDigits(?string $isoOrDial): ?string
    {
        if (! filled($isoOrDial)) {
            return null;
        }

        $value = strtoupper(trim($isoOrDial));

        if (isset(self::catalog()[$value])) {
            return self::catalog()[$value][0];
        }

        $digits = preg_replace('/\D+/', '', $isoOrDial) ?? '';

        return $digits !== '' ? $digits : null;
    }

    public static function flag(string $iso): string
    {
        $iso = strtoupper($iso);

        if (strlen($iso) !== 2 || ! ctype_alpha($iso)) {
            return '🌐';
        }

        $flag = '';

        foreach (str_split($iso) as $letter) {
            $flag .= mb_chr(0x1F1E6 + ord($letter) - ord('A'));
        }

        return $flag;
    }

    /**
     * ISO 3166-1 alpha-2 => [calling code digits, Arabic name, English name]
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    protected static function catalog(): array
    {
        return [
            'AD' => ['376', 'أندورا', 'Andorra'],
            'AE' => ['971', 'الإمارات', 'United Arab Emirates'],
            'AF' => ['93', 'أفغانستان', 'Afghanistan'],
            'AG' => ['1268', 'أنتيغوا وباربودا', 'Antigua and Barbuda'],
            'AI' => ['1264', 'أنغويلا', 'Anguilla'],
            'AL' => ['355', 'ألبانيا', 'Albania'],
            'AM' => ['374', 'أرمينيا', 'Armenia'],
            'AO' => ['244', 'أنغولا', 'Angola'],
            'AR' => ['54', 'الأرجنتين', 'Argentina'],
            'AS' => ['1684', 'ساموا الأمريكية', 'American Samoa'],
            'AT' => ['43', 'النمسا', 'Austria'],
            'AU' => ['61', 'أستراليا', 'Australia'],
            'AW' => ['297', 'أروبا', 'Aruba'],
            'AZ' => ['994', 'أذربيجان', 'Azerbaijan'],
            'BA' => ['387', 'البوسنة والهرسك', 'Bosnia and Herzegovina'],
            'BB' => ['1246', 'بربادوس', 'Barbados'],
            'BD' => ['880', 'بنغلاديش', 'Bangladesh'],
            'BE' => ['32', 'بلجيكا', 'Belgium'],
            'BF' => ['226', 'بوركينا فاسو', 'Burkina Faso'],
            'BG' => ['359', 'بلغاريا', 'Bulgaria'],
            'BH' => ['973', 'البحرين', 'Bahrain'],
            'BI' => ['257', 'بوروندي', 'Burundi'],
            'BJ' => ['229', 'بنين', 'Benin'],
            'BM' => ['1441', 'برمودا', 'Bermuda'],
            'BN' => ['673', 'بروناي', 'Brunei'],
            'BO' => ['591', 'بوليفيا', 'Bolivia'],
            'BR' => ['55', 'البرازيل', 'Brazil'],
            'BS' => ['1242', 'الباهاما', 'Bahamas'],
            'BT' => ['975', 'بوتان', 'Bhutan'],
            'BW' => ['267', 'بوتسوانا', 'Botswana'],
            'BY' => ['375', 'بيلاروس', 'Belarus'],
            'BZ' => ['501', 'بليز', 'Belize'],
            'CA' => ['1', 'كندا', 'Canada'],
            'CD' => ['243', 'الكونغو - كينشاسا', 'Congo (DRC)'],
            'CF' => ['236', 'جمهورية أفريقيا الوسطى', 'Central African Republic'],
            'CG' => ['242', 'الكونغو - برازافيل', 'Congo'],
            'CH' => ['41', 'سويسرا', 'Switzerland'],
            'CI' => ['225', 'ساحل العاج', 'Côte d’Ivoire'],
            'CK' => ['682', 'جزر كوك', 'Cook Islands'],
            'CL' => ['56', 'تشيلي', 'Chile'],
            'CM' => ['237', 'الكاميرون', 'Cameroon'],
            'CN' => ['86', 'الصين', 'China'],
            'CO' => ['57', 'كولومبيا', 'Colombia'],
            'CR' => ['506', 'كوستاريكا', 'Costa Rica'],
            'CU' => ['53', 'كوبا', 'Cuba'],
            'CV' => ['238', 'الرأس الأخضر', 'Cape Verde'],
            'CY' => ['357', 'قبرص', 'Cyprus'],
            'CZ' => ['420', 'التشيك', 'Czechia'],
            'DE' => ['49', 'ألمانيا', 'Germany'],
            'DJ' => ['253', 'جيبوتي', 'Djibouti'],
            'DK' => ['45', 'الدنمارك', 'Denmark'],
            'DM' => ['1767', 'دومينيكا', 'Dominica'],
            'DO' => ['1809', 'جمهورية الدومينيكان', 'Dominican Republic'],
            'DZ' => ['213', 'الجزائر', 'Algeria'],
            'EC' => ['593', 'الإكوادور', 'Ecuador'],
            'EE' => ['372', 'إستونيا', 'Estonia'],
            'EG' => ['20', 'مصر', 'Egypt'],
            'ER' => ['291', 'إريتريا', 'Eritrea'],
            'ES' => ['34', 'إسبانيا', 'Spain'],
            'ET' => ['251', 'إثيوبيا', 'Ethiopia'],
            'FI' => ['358', 'فنلندا', 'Finland'],
            'FJ' => ['679', 'فيجي', 'Fiji'],
            'FK' => ['500', 'جزر فوكلاند', 'Falkland Islands'],
            'FM' => ['691', 'ميكرونيسيا', 'Micronesia'],
            'FO' => ['298', 'جزر فارو', 'Faroe Islands'],
            'FR' => ['33', 'فرنسا', 'France'],
            'GA' => ['241', 'الغابون', 'Gabon'],
            'GB' => ['44', 'المملكة المتحدة', 'United Kingdom'],
            'GD' => ['1473', 'غرينادا', 'Grenada'],
            'GE' => ['995', 'جورجيا', 'Georgia'],
            'GF' => ['594', 'غويانا الفرنسية', 'French Guiana'],
            'GG' => ['44', 'غيرنزي', 'Guernsey'],
            'GH' => ['233', 'غانا', 'Ghana'],
            'GI' => ['350', 'جبل طارق', 'Gibraltar'],
            'GL' => ['299', 'غرينلاند', 'Greenland'],
            'GM' => ['220', 'غامبيا', 'Gambia'],
            'GN' => ['224', 'غينيا', 'Guinea'],
            'GP' => ['590', 'غوادلوب', 'Guadeloupe'],
            'GQ' => ['240', 'غينيا الاستوائية', 'Equatorial Guinea'],
            'GR' => ['30', 'اليونان', 'Greece'],
            'GT' => ['502', 'غواتيمالا', 'Guatemala'],
            'GU' => ['1671', 'غوام', 'Guam'],
            'GW' => ['245', 'غينيا بيساو', 'Guinea-Bissau'],
            'GY' => ['592', 'غيانا', 'Guyana'],
            'HK' => ['852', 'هونغ كونغ', 'Hong Kong'],
            'HN' => ['504', 'هندوراس', 'Honduras'],
            'HR' => ['385', 'كرواتيا', 'Croatia'],
            'HT' => ['509', 'هايتي', 'Haiti'],
            'HU' => ['36', 'المجر', 'Hungary'],
            'ID' => ['62', 'إندونيسيا', 'Indonesia'],
            'IE' => ['353', 'أيرلندا', 'Ireland'],
            'IL' => ['972', 'إسرائيل', 'Israel'],
            'IM' => ['44', 'جزيرة مان', 'Isle of Man'],
            'IN' => ['91', 'الهند', 'India'],
            'IQ' => ['964', 'العراق', 'Iraq'],
            'IR' => ['98', 'إيران', 'Iran'],
            'IS' => ['354', 'آيسلندا', 'Iceland'],
            'IT' => ['39', 'إيطاليا', 'Italy'],
            'JE' => ['44', 'جيرزي', 'Jersey'],
            'JM' => ['1876', 'جامايكا', 'Jamaica'],
            'JO' => ['962', 'الأردن', 'Jordan'],
            'JP' => ['81', 'اليابان', 'Japan'],
            'KE' => ['254', 'كينيا', 'Kenya'],
            'KG' => ['996', 'قيرغيزستان', 'Kyrgyzstan'],
            'KH' => ['855', 'كمبوديا', 'Cambodia'],
            'KI' => ['686', 'كيريباتي', 'Kiribati'],
            'KM' => ['269', 'جزر القمر', 'Comoros'],
            'KN' => ['1869', 'سانت كيتس ونيفيس', 'Saint Kitts and Nevis'],
            'KP' => ['850', 'كوريا الشمالية', 'North Korea'],
            'KR' => ['82', 'كوريا الجنوبية', 'South Korea'],
            'KW' => ['965', 'الكويت', 'Kuwait'],
            'KY' => ['1345', 'جزر كايمان', 'Cayman Islands'],
            'KZ' => ['7', 'كازاخستان', 'Kazakhstan'],
            'LA' => ['856', 'لاوس', 'Laos'],
            'LB' => ['961', 'لبنان', 'Lebanon'],
            'LC' => ['1758', 'سانت لوسيا', 'Saint Lucia'],
            'LI' => ['423', 'ليختنشتاين', 'Liechtenstein'],
            'LK' => ['94', 'سريلانكا', 'Sri Lanka'],
            'LR' => ['231', 'ليبيريا', 'Liberia'],
            'LS' => ['266', 'ليسوتو', 'Lesotho'],
            'LT' => ['370', 'ليتوانيا', 'Lithuania'],
            'LU' => ['352', 'لوكسمبورغ', 'Luxembourg'],
            'LV' => ['371', 'لاتفيا', 'Latvia'],
            'LY' => ['218', 'ليبيا', 'Libya'],
            'MA' => ['212', 'المغرب', 'Morocco'],
            'MC' => ['377', 'موناكو', 'Monaco'],
            'MD' => ['373', 'مولدوفا', 'Moldova'],
            'ME' => ['382', 'الجبل الأسود', 'Montenegro'],
            'MG' => ['261', 'مدغشقر', 'Madagascar'],
            'MH' => ['692', 'جزر مارشال', 'Marshall Islands'],
            'MK' => ['389', 'مقدونيا الشمالية', 'North Macedonia'],
            'ML' => ['223', 'مالي', 'Mali'],
            'MM' => ['95', 'ميانمار', 'Myanmar'],
            'MN' => ['976', 'منغوليا', 'Mongolia'],
            'MO' => ['853', 'ماكاو', 'Macao'],
            'MP' => ['1670', 'جزر ماريانا الشمالية', 'Northern Mariana Islands'],
            'MQ' => ['596', 'مارتينيك', 'Martinique'],
            'MR' => ['222', 'موريتانيا', 'Mauritania'],
            'MS' => ['1664', 'مونتسرات', 'Montserrat'],
            'MT' => ['356', 'مالطا', 'Malta'],
            'MU' => ['230', 'موريشيوس', 'Mauritius'],
            'MV' => ['960', 'جزر المالديف', 'Maldives'],
            'MW' => ['265', 'مالاوي', 'Malawi'],
            'MX' => ['52', 'المكسيك', 'Mexico'],
            'MY' => ['60', 'ماليزيا', 'Malaysia'],
            'MZ' => ['258', 'موزمبيق', 'Mozambique'],
            'NA' => ['264', 'ناميبيا', 'Namibia'],
            'NC' => ['687', 'كاليدونيا الجديدة', 'New Caledonia'],
            'NE' => ['227', 'النيجر', 'Niger'],
            'NF' => ['672', 'جزيرة نورفولك', 'Norfolk Island'],
            'NG' => ['234', 'نيجيريا', 'Nigeria'],
            'NI' => ['505', 'نيكاراغوا', 'Nicaragua'],
            'NL' => ['31', 'هولندا', 'Netherlands'],
            'NO' => ['47', 'النرويج', 'Norway'],
            'NP' => ['977', 'نيبال', 'Nepal'],
            'NR' => ['674', 'ناورو', 'Nauru'],
            'NU' => ['683', 'نيوي', 'Niue'],
            'NZ' => ['64', 'نيوزيلندا', 'New Zealand'],
            'OM' => ['968', 'عُمان', 'Oman'],
            'PA' => ['507', 'بنما', 'Panama'],
            'PE' => ['51', 'بيرو', 'Peru'],
            'PF' => ['689', 'بولينزيا الفرنسية', 'French Polynesia'],
            'PG' => ['675', 'بابوا غينيا الجديدة', 'Papua New Guinea'],
            'PH' => ['63', 'الفلبين', 'Philippines'],
            'PK' => ['92', 'باكستان', 'Pakistan'],
            'PL' => ['48', 'بولندا', 'Poland'],
            'PM' => ['508', 'سان بيير وميكلون', 'Saint Pierre and Miquelon'],
            'PR' => ['1787', 'بورتوريكو', 'Puerto Rico'],
            'PS' => ['970', 'فلسطين', 'Palestine'],
            'PT' => ['351', 'البرتغال', 'Portugal'],
            'PW' => ['680', 'بالاو', 'Palau'],
            'PY' => ['595', 'باراغواي', 'Paraguay'],
            'QA' => ['974', 'قطر', 'Qatar'],
            'RE' => ['262', 'لا ريونيون', 'Réunion'],
            'RO' => ['40', 'رومانيا', 'Romania'],
            'RS' => ['381', 'صربيا', 'Serbia'],
            'RU' => ['7', 'روسيا', 'Russia'],
            'RW' => ['250', 'رواندا', 'Rwanda'],
            'SA' => ['966', 'السعودية', 'Saudi Arabia'],
            'SB' => ['677', 'جزر سليمان', 'Solomon Islands'],
            'SC' => ['248', 'سيشل', 'Seychelles'],
            'SD' => ['249', 'السودان', 'Sudan'],
            'SE' => ['46', 'السويد', 'Sweden'],
            'SG' => ['65', 'سنغافورة', 'Singapore'],
            'SH' => ['290', 'سانت هيلينا', 'Saint Helena'],
            'SI' => ['386', 'سلوفينيا', 'Slovenia'],
            'SK' => ['421', 'سلوفاكيا', 'Slovakia'],
            'SL' => ['232', 'سيراليون', 'Sierra Leone'],
            'SM' => ['378', 'سان مارينو', 'San Marino'],
            'SN' => ['221', 'السنغال', 'Senegal'],
            'SO' => ['252', 'الصومال', 'Somalia'],
            'SR' => ['597', 'سورينام', 'Suriname'],
            'SS' => ['211', 'جنوب السودان', 'South Sudan'],
            'ST' => ['239', 'ساو تومي وبرينسيبي', 'São Tomé and Príncipe'],
            'SV' => ['503', 'السلفادور', 'El Salvador'],
            'SX' => ['1721', 'سانت مارتن', 'Sint Maarten'],
            'SY' => ['963', 'سوريا', 'Syria'],
            'SZ' => ['268', 'إسواتيني', 'Eswatini'],
            'TC' => ['1649', 'جزر توركس وكايكوس', 'Turks and Caicos Islands'],
            'TD' => ['235', 'تشاد', 'Chad'],
            'TG' => ['228', 'توغو', 'Togo'],
            'TH' => ['66', 'تايلاند', 'Thailand'],
            'TJ' => ['992', 'طاجيكستان', 'Tajikistan'],
            'TK' => ['690', 'توكيلاو', 'Tokelau'],
            'TL' => ['670', 'تيمور الشرقية', 'Timor-Leste'],
            'TM' => ['993', 'تركمانستان', 'Turkmenistan'],
            'TN' => ['216', 'تونس', 'Tunisia'],
            'TO' => ['676', 'تونغا', 'Tonga'],
            'TR' => ['90', 'تركيا', 'Türkiye'],
            'TT' => ['1868', 'ترينيداد وتوباغو', 'Trinidad and Tobago'],
            'TV' => ['688', 'توفالو', 'Tuvalu'],
            'TW' => ['886', 'تايوان', 'Taiwan'],
            'TZ' => ['255', 'تنزانيا', 'Tanzania'],
            'UA' => ['380', 'أوكرانيا', 'Ukraine'],
            'UG' => ['256', 'أوغندا', 'Uganda'],
            'US' => ['1', 'الولايات المتحدة', 'United States'],
            'UY' => ['598', 'الأوروغواي', 'Uruguay'],
            'UZ' => ['998', 'أوزبكستان', 'Uzbekistan'],
            'VA' => ['379', 'الفاتيكان', 'Vatican City'],
            'VC' => ['1784', 'سانت فينسنت', 'Saint Vincent and the Grenadines'],
            'VE' => ['58', 'فنزويلا', 'Venezuela'],
            'VG' => ['1284', 'جزر العذراء البريطانية', 'British Virgin Islands'],
            'VI' => ['1340', 'جزر العذراء الأمريكية', 'U.S. Virgin Islands'],
            'VN' => ['84', 'فيتنام', 'Vietnam'],
            'VU' => ['678', 'فانواتو', 'Vanuatu'],
            'WF' => ['681', 'واليس وفوتونا', 'Wallis and Futuna'],
            'WS' => ['685', 'ساموا', 'Samoa'],
            'XK' => ['383', 'كوسوفو', 'Kosovo'],
            'YE' => ['967', 'اليمن', 'Yemen'],
            'YT' => ['262', 'مايوت', 'Mayotte'],
            'ZA' => ['27', 'جنوب أفريقيا', 'South Africa'],
            'ZM' => ['260', 'زامبيا', 'Zambia'],
            'ZW' => ['263', 'زيمبابوي', 'Zimbabwe'],
        ];
    }
}
