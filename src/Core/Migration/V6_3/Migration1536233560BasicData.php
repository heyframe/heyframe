<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1536233560BasicData extends MigrationStep
{
    private ?string $enGbLanguageId = null;

    public function getCreationTimestamp(): int
    {
        return 1536233560;
    }

    public function update(Connection $connection): void
    {
        $hasData = $connection->executeQuery('SELECT 1 FROM `language` LIMIT 1')->fetchAssociative();
        if ($hasData) {
            return;
        }
        $this->createLanguage($connection);
        $this->createLocale($connection);
        $this->createCountry($connection);
        $this->createCurrency($connection);
        $this->createCustomerGroup($connection);
    }

    private function createCustomerGroup(Connection $connection): void
    {
        $connection->insert('customer_group', ['id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('customer_group_translation', ['customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()), 'name' => 'Standard customer group', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('customer_group_translation', ['customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM), 'name' => '普通客户组', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCurrency(Connection $connection): void
    {
        $CNY = Uuid::fromHexToBytes(Defaults::CURRENCY);
        $USD = Uuid::randomBytes();
        $GBP = Uuid::randomBytes();
        $EUR = Uuid::randomBytes();

        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $connection->insert('currency', ['id' => $CNY, 'iso_code' => 'CNY', 'factor' => 1, 'symbol' => '¥', 'position' => 1,  'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $CNY, 'language_id' => $languageEN, 'short_name' => 'CNY', 'name' => 'CNY', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $CNY, 'language_id' => $languageZH, 'short_name' => 'CNY', 'name' => '人民币', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $EUR, 'iso_code' => 'EUR', 'factor' => 0.1205, 'symbol' => '€', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $EUR, 'language_id' => $languageEN, 'short_name' => 'EUR', 'name' => 'Euro', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $EUR, 'language_id' => $languageZH, 'short_name' => 'EUR', 'name' => '欧元', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $USD, 'iso_code' => 'USD', 'factor' => 0.1396, 'symbol' => '$', 'position' => 1,  'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $USD, 'language_id' => $languageEN, 'short_name' => 'USD', 'name' => 'US-Dollar', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $USD, 'language_id' => $languageZH, 'short_name' => 'USD', 'name' => '美元', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $GBP, 'iso_code' => 'GBP', 'factor' => 0.1039, 'symbol' => '£', 'position' => 1,  'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $GBP, 'language_id' => $languageEN, 'short_name' => 'GBP', 'name' => 'Pound', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $GBP, 'language_id' => $languageZH, 'short_name' => 'GBP', 'name' => '英镑', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCountry(Connection $connection): void
    {
        $languageZH = static fn (string $countryId, string $name) => [
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'name' => $name,
            'country_id' => $countryId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $languageEN = fn (string $countryId, string $name) => [
            'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
            'name' => $name,
            'country_id' => $countryId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $zhId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $zhId, 'iso' => 'CN', 'position' => 1, 'iso3' => 'CHN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZH($zhId, '中国'));
        $connection->insert('country_translation', $languageEN($zhId, 'China'));
        $this->createCountryStates($connection, $zhId, 'CN');

        $deId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $deId, 'iso' => 'DE', 'position' => 10, 'iso3' => 'DEU', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZH($deId, '德国'));
        $connection->insert('country_translation', $languageEN($deId, 'Germany'));

        $this->createCountryStates($connection, $deId, 'DE');

        $grId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $grId, 'iso' => 'GR', 'position' => 10, 'iso3' => 'GRC', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($grId, 'Greece'));
        $connection->insert('country_translation', $languageZH($grId, '希腊'));

        $gbId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $gbId, 'iso' => 'GB', 'position' => 5, 'iso3' => 'GBR', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($gbId, 'Great Britain'));
        $connection->insert('country_translation', $languageZH($gbId, '英国'));

        $this->createCountryStates($connection, $gbId, 'GB');

        $ieId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $ieId, 'iso' => 'IE', 'position' => 10, 'iso3' => 'IRL', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($ieId, 'Ireland'));
        $connection->insert('country_translation', $languageZH($ieId, '爱尔兰'));

        $isId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $isId, 'iso' => 'IS', 'position' => 10, 'iso3' => 'ISL', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($isId, 'Iceland'));
        $connection->insert('country_translation', $languageZH($isId, '冰岛'));

        $itId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $itId, 'iso' => 'IT', 'position' => 10, 'active' => 1, 'iso3' => 'ITA', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($itId, 'Italy'));
        $connection->insert('country_translation', $languageZH($itId, '意大利'));

        $jpId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $jpId, 'iso' => 'JP', 'position' => 10, 'iso3' => 'JPN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($jpId, 'Japan'));
        $connection->insert('country_translation', $languageZH($jpId, '日本'));

        $caId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $caId, 'iso' => 'CA', 'position' => 10, 'iso3' => 'CAN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($caId, 'Canada'));
        $connection->insert('country_translation', $languageZH($caId, '加拿大'));

        $luId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $luId, 'iso' => 'LU', 'position' => 10, 'iso3' => 'LUX', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($luId, 'Luxembourg'));
        $connection->insert('country_translation', $languageZH($luId, '卢森堡'));

        $naId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $naId, 'iso' => 'NA', 'position' => 10, 'iso3' => 'NAM', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($naId, 'Namibia'));
        $connection->insert('country_translation', $languageZH($naId, '纳米比亚'));

        $nlId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $nlId, 'iso' => 'NL', 'position' => 10, 'active' => 1, 'iso3' => 'NLD', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($nlId, 'Netherlands'));
        $connection->insert('country_translation', $languageZH($nlId, '荷兰'));

        $noId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $noId, 'iso' => 'NO', 'position' => 10, 'iso3' => 'NOR', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($noId, 'Norway'));
        $connection->insert('country_translation', $languageZH($noId, '挪威'));

        $atId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $atId, 'iso' => 'AT', 'position' => 10, 'active' => 1, 'iso3' => 'AUT', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($atId, 'Austria'));
        $connection->insert('country_translation', $languageZH($atId, '奥地利'));

        $ptId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $ptId, 'iso' => 'PT', 'position' => 10, 'iso3' => 'PRT', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($ptId, 'Portugal'));
        $connection->insert('country_translation', $languageZH($ptId, '葡萄牙'));

        $seId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $seId, 'iso' => 'SE', 'position' => 10, 'iso3' => 'SWE', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($seId, 'Sweden'));
        $connection->insert('country_translation', $languageZH($seId, '瑞典'));

        $chId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $chId, 'iso' => 'CH', 'position' => 10, 'active' => 1, 'iso3' => 'CHE', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($chId, 'Switzerland'));
        $connection->insert('country_translation', $languageZH($chId, '瑞士'));

        $esId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $esId, 'iso' => 'ES', 'position' => 10, 'active' => 1, 'iso3' => 'ESP', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($esId, 'Spain'));
        $connection->insert('country_translation', $languageZH($esId, '西班牙'));

        $usId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $usId, 'iso' => 'US', 'position' => 10, 'iso3' => 'USA', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($usId, 'USA'));
        $connection->insert('country_translation', $languageZH($usId, '美国'));

        $this->createCountryStates($connection, $usId, 'US');

        $liId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $liId, 'iso' => 'LI', 'position' => 10, 'iso3' => 'LIE', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($liId, 'Liechtenstein'));
        $connection->insert('country_translation', $languageZH($liId, 'Liechtenstein'));

        $aeId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $aeId, 'iso' => 'AE', 'position' => 10, 'active' => 1, 'iso3' => 'ARE', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($aeId, 'Arab Emirates'));
        $connection->insert('country_translation', $languageZH($aeId, '阿拉伯联合酋长国'));

        $plId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $plId, 'iso' => 'PL', 'position' => 10, 'iso3' => 'POL', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($plId, 'Poland'));
        $connection->insert('country_translation', $languageZH($plId, '波兰'));

        $huId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $huId, 'iso' => 'HU', 'position' => 10, 'iso3' => 'HUN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($huId, 'Hungary'));
        $connection->insert('country_translation', $languageZH($huId, '匈牙利'));

        $trId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $trId, 'iso' => 'TR', 'position' => 10, 'iso3' => 'TUR', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($trId, 'Turkey'));
        $connection->insert('country_translation', $languageZH($trId, '土耳其'));

        $czId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $czId, 'iso' => 'CZ', 'position' => 10, 'iso3' => 'CZE', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($czId, 'Czech Republic'));
        $connection->insert('country_translation', $languageZH($czId, '捷克'));

        $skId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $skId, 'iso' => 'SK', 'position' => 10, 'iso3' => 'SVK', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($skId, 'Slovenia'));
        $connection->insert('country_translation', $languageZH($skId, '斯洛文尼亚'));

        $roId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $roId, 'iso' => 'RO', 'position' => 10, 'iso3' => 'ROU', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($roId, 'Romania'));
        $connection->insert('country_translation', $languageZH($roId, '罗马尼亚'));

        $brId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $brId, 'iso' => 'BR', 'position' => 10, 'iso3' => 'BRA', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($brId, 'Brazil'));
        $connection->insert('country_translation', $languageZH($brId, '巴西'));

        $ilId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $ilId, 'iso' => 'IL', 'position' => 10, 'iso3' => 'ISR', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($ilId, 'Israel'));
        $connection->insert('country_translation', $languageZH($ilId, '以色列'));

        $auId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $auId, 'iso' => 'AU', 'position' => 10, 'active' => 1, 'iso3' => 'AUS', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($auId, 'Australia'));
        $connection->insert('country_translation', $languageZH($auId, '澳大利亚'));

        $beId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $beId, 'iso' => 'BE', 'position' => 10, 'active' => 1, 'iso3' => 'BEL', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($beId, 'Belgium'));
        $connection->insert('country_translation', $languageZH($beId, '比利时'));

        $dkId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $dkId, 'iso' => 'DK', 'position' => 10, 'active' => 1, 'iso3' => 'DNK', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($dkId, 'Denmark'));
        $connection->insert('country_translation', $languageZH($dkId, '丹麦'));

        $fiId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $fiId, 'iso' => 'FI', 'position' => 10, 'active' => 1, 'iso3' => 'FIN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($fiId, 'Finland'));
        $connection->insert('country_translation', $languageZH($fiId, '芬兰'));

        $frId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $frId, 'iso' => 'FR', 'position' => 10, 'iso3' => 'FRA', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEN($frId, 'France'));
        $connection->insert('country_translation', $languageZH($frId, '法国'));
    }

    private function createCountryStates(Connection $connection, string $countryId, string $countryCode): void
    {
        $data = [
            'CN' => [
                'BJ' => '北京市',
                'TJ' => '天津市',
                'HE' => '河北省',
                'SX' => '山西省',
                'NM' => '内蒙古自治区',
                'LN' => '辽宁省',
                'JL' => '吉林省',
                'HL' => '黑龙江省',
                'SH' => '上海市',
                'JS' => '江苏省',
                'ZJ' => '浙江省',
                'AH' => '安徽省',
                'FJ' => '福建省',
                'JX' => '江西省',
                'SD' => '山东省',
                'HA' => '河南省',
                'HB' => '湖北省',
                'HN' => '湖南省',
                'GD' => '广东省',
                'GX' => '广西壮族自治区',
                'HI' => '海南省',
                'CQ' => '重庆市',
                'SC' => '四川省',
                'GZ' => '贵州省',
                'YN' => '云南省',
                'XZ' => '西藏自治区',
                'SN' => '陕西省',
                'GS' => '甘肃省',
                'QH' => '青海省',
                'NX' => '宁夏回族自治区',
                'XJ' => '新疆维吾尔自治区',
                'TW' => '台湾省',
                'HK' => '香港特别行政区',
                'MO' => '澳门特别行政区',
            ],
            'US' => [
                'US-AL' => '阿拉巴马州',
                'US-AK' => '阿拉斯加州',
                'US-AZ' => '亚利桑那州',
                'US-AR' => '阿肯色州',
                'US-CA' => '加利福尼亚州',
                'US-CO' => '科罗拉多州',
                'US-CT' => '康涅狄格州',
                'US-DE' => '特拉华州',
                'US-FL' => '佛罗里达州',
                'US-GA' => '佐治亚州',
                'US-HI' => '夏威夷州',
                'US-ID' => '爱达荷州',
                'US-IL' => '伊利诺伊州',
                'US-IN' => '印第安纳州',
                'US-IA' => '艾奥瓦州',
                'US-KS' => '堪萨斯州',
                'US-KY' => '肯塔基州',
                'US-LA' => '路易斯安那州',
                'US-ME' => '缅因州',
                'US-MD' => '马里兰州',
                'US-MA' => '马萨诸塞州',
                'US-MI' => '密歇根州',
                'US-MN' => '明尼苏达州',
                'US-MS' => '密西西比州',
                'US-MO' => '密苏里州',
                'US-MT' => '蒙大拿州',
                'US-NE' => '内布拉斯加州',
                'US-NV' => '内华达州',
                'US-NH' => '新罕布什尔州',
                'US-NJ' => '新泽西州',
                'US-NM' => '新墨西哥州',
                'US-NY' => '纽约州',
                'US-NC' => '北卡罗来纳州',
                'US-ND' => '北达科他州',
                'US-OH' => '俄亥俄州',
                'US-OK' => '俄克拉荷马州',
                'US-OR' => '俄勒冈州',
                'US-PA' => '宾夕法尼亚州',
                'US-RI' => '罗得岛州',
                'US-SC' => '南卡罗来纳州',
                'US-SD' => '南达科他州',
                'US-TN' => '田纳西州',
                'US-TX' => '德克萨斯州',
                'US-UT' => '犹他州',
                'US-VT' => '佛蒙特州',
                'US-VA' => '弗吉尼亚州',
                'US-WA' => '华盛顿州',
                'US-WV' => '西弗吉尼亚州',
                'US-WI' => '威斯康星州',
                'US-WY' => '怀俄明州',
                'US-DC' => '哥伦比亚特区',
            ],
            'DE' => [
                'DE-BW' => '巴登-符腾堡州',
                'DE-BY' => '巴伐利亚州',
                'DE-BE' => '柏林',
                'DE-BB' => '勃兰登堡州',
                'DE-HB' => '不来梅州',
                'DE-HH' => '汉堡',
                'DE-HE' => '黑森州',
                'DE-NI' => '下萨克森州',
                'DE-MV' => '梅克伦堡-前波美拉尼亚州',
                'DE-NW' => '北莱茵-威斯特法伦州',
                'DE-RP' => '莱茵兰-普法尔茨州',
                'DE-SL' => '萨尔州',
                'DE-SN' => '萨克森州',
                'DE-ST' => '萨克森-安哈尔特州',
                'DE-SH' => '石勒苏益格-荷尔斯泰因州',
                'DE-TH' => '图林根州',
            ],
            'GB' => [
                'GB-ENG' => '英格兰',
                'GB-NIR' => '北爱尔兰',
                'GB-SCT' => '苏格兰',
                'GB-WLS' => '威尔士',
                'GB-EAW' => '英格兰和威尔士',
                'GB-GBN' => '大不列颠',
                'GB-UKM' => '联合王国',
                'GB-BKM' => '白金汉郡',
                'GB-CAM' => '剑桥郡',
                'GB-CMA' => '坎布里亚郡',
                'GB-DBY' => '德比郡',
                'GB-DEV' => '德文郡',
                'GB-DOR' => '多塞特郡',
                'GB-ESX' => '东萨塞克斯郡',
                'GB-ESS' => '埃塞克斯郡',
                'GB-GLS' => '格洛斯特郡',
                'GB-HAM' => '汉普郡',
                'GB-HRT' => '赫特福德郡',
                'GB-KEN' => '肯特郡',
                'GB-LAN' => '兰开夏郡',
                'GB-LEC' => '莱斯特郡',
                'GB-LIN' => '林肯郡',
                'GB-NFK' => '诺福克郡',
                'GB-NYK' => '北约克郡',
                'GB-NTH' => '北安普敦郡',
                'GB-NTT' => '诺丁汉郡',
                'GB-OXF' => '牛津郡',
                'GB-SOM' => '萨默塞特郡',
                'GB-STS' => '斯塔福德郡',
                'GB-SFK' => '萨福克郡',
                'GB-SRY' => '萨里郡',
                'GB-WAR' => '沃里克郡',
                'GB-WSX' => '西萨塞克斯郡',
                'GB-WOR' => '伍斯特郡',
                'GB-LND' => '伦敦市',
                'GB-BDG' => '巴金和达格纳姆区',
                'GB-BNE' => '巴尼特区',
                'GB-BEX' => '贝克斯利区',
                'GB-BEN' => '布伦特区',
                'GB-BRY' => '布罗姆利区',
                'GB-CMD' => '卡姆登区',
                'GB-CRY' => '克罗伊登区',
                'GB-EAL' => '伊灵区',
                'GB-ENF' => '恩菲尔德区',
                'GB-GRE' => '格林尼治区',
                'GB-HCK' => '哈克尼区',
                'GB-HMF' => '哈默史密斯和富勒姆区',
                'GB-HRY' => '哈林盖区',
                'GB-HRW' => '哈罗区',
                'GB-HAV' => '哈弗灵区',
                'GB-HIL' => '希灵登区',
                'GB-HNS' => '豪恩斯洛区',
                'GB-ISL' => '伊斯灵顿区',
                'GB-KEC' => '肯辛顿和切尔西区',
                'GB-KTT' => '泰晤士河畔金斯敦区',
                'GB-LBH' => '兰贝斯区',
                'GB-LEW' => '刘易舍姆区',
                'GB-MRT' => '默顿区',
                'GB-NWM' => '纽汉区',
                'GB-RDB' => '雷德布里奇区',
                'GB-RIC' => '泰晤士河畔里士满区',
                'GB-SWK' => '绍斯华克区',
                'GB-STN' => '萨顿区',
                'GB-TWH' => '塔村区',
                'GB-WFT' => '沃尔瑟姆森林区',
                'GB-WND' => '旺兹沃思区',
                'GB-WSM' => '威斯敏斯特区',
                'GB-BIR' => '伯明翰',
                'GB-MAN' => '曼彻斯特',
                'GB-LIV' => '利物浦',
                'GB-LDS' => '利兹',
                'GB-SHF' => '谢菲尔德',
                'GB-NGM' => '诺丁汉',
                'GB-COV' => '考文垂',
                'GB-BRD' => '布拉德福德',
                'GB-BFS' => '贝尔法斯特',
                'GB-DRS' => '德里和斯特拉班',
                'GB-CCG' => '科兹韦海岸和格伦斯',
                'GB-NMD' => '纽里、莫恩和唐区',
                'GB-MEA' => '中东安特里姆区',
                'GB-ABE' => '阿伯丁市',
                'GB-ABD' => '阿伯丁郡',
                'GB-ANS' => '安格斯',
                'GB-AGB' => '阿盖尔-比特',
                'GB-EDH' => '爱丁堡市',
                'GB-GLG' => '格拉斯哥市',
                'GB-FIF' => '法夫',
                'GB-HLD' => '高地',
                'GB-ORK' => '奥克尼群岛',
                'GB-ZET' => '设得兰群岛',
                'GB-ELS' => '外赫布里底群岛',
                'GB-CRF' => '加的夫',
                'GB-SWA' => '斯旺西',
                'GB-NWP' => '纽波特',
                'GB-CMN' => '卡马森郡',
                'GB-PEM' => '彭布罗克郡',
                'GB-GWN' => '格温内斯',
                'GB-AGY' => '安格尔西岛',
                'GB-RCT' => '朗达-卡嫩-塔夫',
                'GB-VGL' => '格拉摩根谷',
            ],
        ];
        $chineseTranslations = [
            'CN' => [
                'BJ' => 'Beijing',
                'TJ' => 'Tianjin',
                'HE' => 'Hebei',
                'SX' => 'Shanxi',
                'NM' => 'Inner Mongoria',
                'LN' => 'Liaoning',
                'JL' => 'Jilin',
                'HL' => 'Heilongjiang',
                'SH' => 'Shanghai',
                'JS' => 'Jiangsu',
                'ZJ' => 'Zhejiang',
                'AH' => 'Anhui',
                'FJ' => 'Fujian',
                'JX' => 'Jiangxi',
                'SD' => 'Shandong',
                'HA' => 'Henan',
                'HB' => 'Hubei',
                'HN' => 'Hunan',
                'GD' => 'Guangdong',
                'GX' => 'Guangxi',
                'HI' => 'Hainan',
                'CQ' => 'Chongqing',
                'SC' => 'Sichuan',
                'GZ' => 'Guizhou',
                'YN' => 'Yunnan',
                'XZ' => 'Tibet',
                'SN' => 'Shanxi',
                'GS' => 'Gansu',
                'QH' => 'Qinghai',
                'NX' => 'Ningxia',
                'XJ' => 'Xinjiang',
                'TW' => 'Taiwan',
                'HK' => 'Hong Kong',
                'MO' => 'Macao',
            ],
            'US' => [
                'US-AL' => 'Alabama',
                'US-AK' => 'Alaska',
                'US-AZ' => 'Arizona',
                'US-AR' => 'Arkansas',
                'US-CA' => 'California',
                'US-CO' => 'Colorado',
                'US-CT' => 'Connecticut',
                'US-DE' => 'Delaware',
                'US-FL' => 'Florida',
                'US-GA' => 'Georgia',
                'US-HI' => 'Hawaii',
                'US-ID' => 'Idaho',
                'US-IL' => 'Illinois',
                'US-IN' => 'Indiana',
                'US-IA' => 'Iowa',
                'US-KS' => 'Kansas',
                'US-KY' => 'Kentucky',
                'US-LA' => 'Louisiana',
                'US-ME' => 'Maine',
                'US-MD' => 'Maryland',
                'US-MA' => 'Massachusetts',
                'US-MI' => 'Michigan',
                'US-MN' => 'Minnesota',
                'US-MS' => 'Mississippi',
                'US-MO' => 'Missouri',
                'US-MT' => 'Montana',
                'US-NE' => 'Nebraska',
                'US-NV' => 'Nevada',
                'US-NH' => 'New Hampshire',
                'US-NJ' => 'New Jersey',
                'US-NM' => 'New Mexico',
                'US-NY' => 'New York',
                'US-NC' => 'North Carolina',
                'US-ND' => 'North Dakota',
                'US-OH' => 'Ohio',
                'US-OK' => 'Oklahoma',
                'US-OR' => 'Oregon',
                'US-PA' => 'Pennsylvania',
                'US-RI' => 'Rhode Island',
                'US-SC' => 'South Carolina',
                'US-SD' => 'South Dakota',
                'US-TN' => 'Tennessee',
                'US-TX' => 'Texas',
                'US-UT' => 'Utah',
                'US-VT' => 'Vermont',
                'US-VA' => 'Virginia',
                'US-WA' => 'Washington',
                'US-WV' => 'West Virginia',
                'US-WI' => 'Wisconsin',
                'US-WY' => 'Wyoming',
                'US-DC' => 'District of Columbia',
            ],
            'DE' => [
                'DE-BW' => 'Baden-Württemberg',
                'DE-BY' => 'Bavaria',
                'DE-BE' => 'Berlin',
                'DE-BB' => 'Brandenburg',
                'DE-HB' => 'Bremen',
                'DE-HH' => 'Hamburg',
                'DE-HE' => 'Hesse',
                'DE-NI' => 'Lower Saxony',
                'DE-MV' => 'Mecklenburg-Western Pomerania',
                'DE-NW' => 'North Rhine-Westphalia',
                'DE-RP' => 'Rhineland-Palatinate',
                'DE-SL' => 'Saarland',
                'DE-SN' => 'Saxony',
                'DE-ST' => 'Saxony-Anhalt',
                'DE-SH' => 'Schleswig-Holstein',
                'DE-TH' => 'Thuringia',
            ],
            'GB' => [
                'GB-ENG' => 'England',
                'GB-NIR' => 'Northern Ireland',
                'GB-SCT' => 'Scotland',
                'GB-WLS' => 'Wales',

                'GB-EAW' => 'England and Wales',
                'GB-GBN' => 'Great Britain',
                'GB-UKM' => 'United Kingdom',

                'GB-BKM' => 'Buckinghamshire',
                'GB-CAM' => 'Cambridgeshire',
                'GB-CMA' => 'Cumbria',
                'GB-DBY' => 'Derbyshire',
                'GB-DEV' => 'Devon',
                'GB-DOR' => 'Dorset',
                'GB-ESX' => 'East Sussex',
                'GB-ESS' => 'Essex',
                'GB-GLS' => 'Gloucestershire',
                'GB-HAM' => 'Hampshire',
                'GB-HRT' => 'Hertfordshire',
                'GB-KEN' => 'Kent',
                'GB-LAN' => 'Lancashire',
                'GB-LEC' => 'Leicestershire',
                'GB-LIN' => 'Lincolnshire',
                'GB-NFK' => 'Norfolk',
                'GB-NYK' => 'North Yorkshire',
                'GB-NTH' => 'Northamptonshire',
                'GB-NTT' => 'Nottinghamshire',
                'GB-OXF' => 'Oxfordshire',
                'GB-SOM' => 'Somerset',
                'GB-STS' => 'Staffordshire',
                'GB-SFK' => 'Suffolk',
                'GB-SRY' => 'Surrey',
                'GB-WAR' => 'Warwickshire',
                'GB-WSX' => 'West Sussex',
                'GB-WOR' => 'Worcestershire',
                'GB-LND' => 'London, City of',
                'GB-BDG' => 'Barking and Dagenham',
                'GB-BNE' => 'Barnet',
                'GB-BEX' => 'Bexley',
                'GB-BEN' => 'Brent',
                'GB-BRY' => 'Bromley',
                'GB-CMD' => 'Camden',
                'GB-CRY' => 'Croydon',
                'GB-EAL' => 'Ealing',
                'GB-ENF' => 'Enfield',
                'GB-GRE' => 'Greenwich',
                'GB-HCK' => 'Hackney',
                'GB-HMF' => 'Hammersmith and Fulham',
                'GB-HRY' => 'Haringey',
                'GB-HRW' => 'Harrow',
                'GB-HAV' => 'Havering',
                'GB-HIL' => 'Hillingdon',
                'GB-HNS' => 'Hounslow',
                'GB-ISL' => 'Islington',
                'GB-KEC' => 'Kensington and Chelsea',
                'GB-KTT' => 'Kingston upon Thames',
                'GB-LBH' => 'Lambeth',
                'GB-LEW' => 'Lewisham',
                'GB-MRT' => 'Merton',
                'GB-NWM' => 'Newham',
                'GB-RDB' => 'Redbridge',
                'GB-RIC' => 'Richmond upon Thames',
                'GB-SWK' => 'Southwark',
                'GB-STN' => 'Sutton',
                'GB-TWH' => 'Tower Hamlets',
                'GB-WFT' => 'Waltham Forest',
                'GB-WND' => 'Wandsworth',
                'GB-WSM' => 'Westminster',
                'GB-BNS' => 'Barnsley',
                'GB-BIR' => 'Birmingham',
                'GB-BOL' => 'Bolton',
                'GB-BRD' => 'Bradford',
                'GB-BUR' => 'Bury',
                'GB-CLD' => 'Calderdale',
                'GB-COV' => 'Coventry',
                'GB-DNC' => 'Doncaster',
                'GB-DUD' => 'Dudley',
                'GB-GAT' => 'Gateshead',
                'GB-KIR' => 'Kirklees',
                'GB-KWL' => 'Knowsley',
                'GB-LDS' => 'Leeds',
                'GB-LIV' => 'Liverpool',
                'GB-MAN' => 'Manchester',
                'GB-NET' => 'Newcastle upon Tyne',
                'GB-NTY' => 'North Tyneside',
                'GB-OLD' => 'Oldham',
                'GB-RCH' => 'Rochdale',
                'GB-ROT' => 'Rotherham',
                'GB-SHN' => 'St. Helens',
                'GB-SLF' => 'Salford',
                'GB-SAW' => 'Sandwell',
                'GB-SFT' => 'Sefton',
                'GB-SHF' => 'Sheffield',
                'GB-SOL' => 'Solihull',
                'GB-STY' => 'South Tyneside',
                'GB-SKP' => 'Stockport',
                'GB-SND' => 'Sunderland',
                'GB-TAM' => 'Tameside',
                'GB-TRF' => 'Trafford',
                'GB-WKF' => 'Wakefield',
                'GB-WLL' => 'Walsall',
                'GB-WGN' => 'Wigan',
                'GB-WRL' => 'Wirral',
                'GB-WLV' => 'Wolverhampton',
                'GB-BAS' => 'Bath and North East Somerset',
                'GB-BDF' => 'Bedford',
                'GB-BBD' => 'Blackburn with Darwen',
                'GB-BPL' => 'Blackpool',
                'GB-BMH' => 'Bournemouth',
                'GB-BRC' => 'Bracknell Forest',
                'GB-BNH' => 'Brighton and Hove',
                'GB-BST' => 'Bristol, City of',
                'GB-CBF' => 'Central Bedfordshire',
                'GB-CHE' => 'Cheshire East',
                'GB-CHW' => 'Cheshire West and Chester',
                'GB-CON' => 'Cornwall',
                'GB-DAL' => 'Darlington',
                'GB-DER' => 'Derby',
                'GB-DUR' => 'Durham County',
                'GB-ERY' => 'East Riding of Yorkshire',
                'GB-HAL' => 'Halton',
                'GB-HPL' => 'Hartlepool',
                'GB-HEF' => 'Herefordshire',
                'GB-IOW' => 'Isle of Wight',
                'GB-IOS' => 'Isles of Scilly',
                'GB-KHL' => 'Kingston upon Hull',
                'GB-LCE' => 'Leicester',
                'GB-LUT' => 'Luton',
                'GB-MDW' => 'Medway',
                'GB-MDB' => 'Middlesbrough',
                'GB-MIK' => 'Milton Keynes',
                'GB-NEL' => 'North East Lincolnshire',
                'GB-NLN' => 'North Lincolnshire',
                'GB-NSM' => 'North Somerset',
                'GB-NBL' => 'Northumberland',
                'GB-NGM' => 'Nottingham',
                'GB-PTE' => 'Peterborough',
                'GB-PLY' => 'Plymouth',
                'GB-POL' => 'Poole',
                'GB-POR' => 'Portsmouth',
                'GB-RDG' => 'Reading',
                'GB-RCC' => 'Redcar and Cleveland',
                'GB-RUT' => 'Rutland',
                'GB-SHR' => 'Shropshire',
                'GB-SLG' => 'Slough',
                'GB-SGC' => 'South Gloucestershire',
                'GB-STH' => 'Southampton',
                'GB-SOS' => 'Southend-on-Sea',
                'GB-STT' => 'Stockton-on-Tees',
                'GB-STE' => 'Stoke-on-Trent',
                'GB-SWD' => 'Swindon',
                'GB-TFW' => 'Telford and Wrekin',
                'GB-THR' => 'Thurrock',
                'GB-TOB' => 'Torbay',
                'GB-WRT' => 'Warrington',
                'GB-WBK' => 'West Berkshire',
                'GB-WIL' => 'Wiltshire',
                'GB-WNM' => 'Windsor and Maidenhead',
                'GB-WOK' => 'Wokingham',
                'GB-YOR' => 'York',
                'GB-ANN' => 'Antrim and Newtownabbey',
                'GB-AND' => 'Ards and North Down',
                'GB-ABC' => 'Armagh, Banbridge and Craigavon',
                'GB-BFS' => 'Belfast',
                'GB-CCG' => 'Causeway Coast and Glens',
                'GB-DRS' => 'Derry and Strabane',
                'GB-FMO' => 'Fermanagh and Omagh',
                'GB-LBC' => 'Lisburn and Castlereagh',
                'GB-MEA' => 'Mid and East Antrim',
                'GB-MUL' => 'Mid Ulster',
                'GB-NMD' => 'Newry, Mourne and Down',
                'GB-ABE' => 'Aberdeen City',
                'GB-ABD' => 'Aberdeenshire',
                'GB-ANS' => 'Angus',
                'GB-AGB' => 'Argyll and Bute',
                'GB-CLK' => 'Clackmannanshire',
                'GB-DGY' => 'Dumfries and Galloway',
                'GB-DND' => 'Dundee City',
                'GB-EAY' => 'East Ayrshire',
                'GB-EDU' => 'East Dunbartonshire',
                'GB-ELN' => 'East Lothian',
                'GB-ERW' => 'East Renfrewshire',
                'GB-EDH' => 'Edinburgh, City of',
                'GB-ELS' => 'Eilean Siar',
                'GB-FAL' => 'Falkirk',
                'GB-FIF' => 'Fife',
                'GB-GLG' => 'Glasgow City',
                'GB-HLD' => 'Highland',
                'GB-IVC' => 'Inverclyde',
                'GB-MLN' => 'Midlothian',
                'GB-MRY' => 'Moray',
                'GB-NAY' => 'North Ayrshire',
                'GB-NLK' => 'North Lanarkshire',
                'GB-ORK' => 'Orkney Islands',
                'GB-PKN' => 'Perth and Kinross',
                'GB-RFW' => 'Renfrewshire',
                'GB-SCB' => 'Scottish Borders, The',
                'GB-ZET' => 'Shetland Islands',
                'GB-SAY' => 'South Ayrshire',
                'GB-SLK' => 'South Lanarkshire',
                'GB-STG' => 'Stirling',
                'GB-WDU' => 'West Dunbartonshire',
                'GB-WLN' => 'West Lothian',
                'GB-BGW' => 'Blaenau Gwent',
                'GB-BGE' => 'Bridgend',
                'GB-CAY' => 'Caerphilly',
                'GB-CRF' => 'Cardiff',
                'GB-CMN' => 'Carmarthenshire',
                'GB-CGN' => 'Ceredigion',
                'GB-CWY' => 'Conwy',
                'GB-DEN' => 'Denbighshire',
                'GB-FLN' => 'Flintshire',
                'GB-GWN' => 'Gwynedd',
                'GB-AGY' => 'Isle of Anglesey',
                'GB-MTY' => 'Merthyr Tydfil',
                'GB-MON' => 'Monmouthshire',
                'GB-NTL' => 'Neath Port Talbot',
                'GB-NWP' => 'Newport',
                'GB-PEM' => 'Pembrokeshire',
                'GB-POW' => 'Powys',
                'GB-RCT' => 'Rhondda, Cynon, Taff',
                'GB-SWA' => 'Swansea',
                'GB-TOF' => 'Torfaen',
                'GB-VGL' => 'Vale of Glamorgan, The',
                'GB-WRX' => 'Wrexham',
            ],
        ];

        foreach ($data[$countryCode] as $isoCode => $name) {
            $storageDate = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $id = Uuid::randomBytes();
            $countryStateData = [
                'id' => $id,
                'country_id' => $countryId,
                'short_code' => $isoCode,
                'created_at' => $storageDate,
            ];
            $connection->insert('country_state', $countryStateData);
            $connection->insert('country_state_translation', [
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'country_state_id' => $id,
                'name' => $name,
                'created_at' => $storageDate,
            ]);

            if (isset($chineseTranslations[$countryCode])) {
                $connection->insert('country_state_translation', [
                    'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
                    'country_state_id' => $id,
                    'name' => $name,
                    'created_at' => $storageDate,
                ]);
            }
        }
    }

    private function createLocale(Connection $connection): void
    {
        $localeData = include __DIR__ . '/../../locales.php';

        $queue = new MultiInsertQueryQueue($connection);
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        foreach ($localeData as $locale) {
            if (\in_array($locale['locale'], ['en-GB', 'zh-CN'], true)) {
                continue;
            }

            $localeId = Uuid::randomBytes();

            $queue->addInsert(
                'locale',
                ['id' => $localeId, 'code' => $locale['locale'], 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]
            );

            $queue->addInsert(
                'locale_translation',
                [
                    'locale_id' => $localeId,
                    'language_id' => $languageZh,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'name' => $locale['name']['en-GB'],
                    'territory' => $locale['territory']['en-GB'],
                ]
            );

            $queue->addInsert(
                'locale_translation',
                [
                    'locale_id' => $localeId,
                    'language_id' => $languageEn,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'name' => $locale['name']['zh-CN'],
                    'territory' => $locale['territory']['zh-CN'],
                ]
            );
        }

        $queue->execute();
    }

    private function createLanguage(Connection $connection): void
    {
        $localeEn = Uuid::randomBytes();
        $localeZh = Uuid::randomBytes();
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        // first locales
        $connection->insert('locale', ['id' => $localeEn, 'code' => 'en-GB', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('locale', ['id' => $localeZh, 'code' => 'zh-CN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // second languages
        $connection->insert('language', [
            'id' => $languageEn,
            'name' => 'English',
            'locale_id' => $localeEn,
            'translation_code_id' => $localeEn,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('language', [
            'id' => $languageZh,
            'name' => '中文',
            'locale_id' => $localeZh,
            'translation_code_id' => $localeZh,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // third translations
        $connection->insert('locale_translation', [
            'locale_id' => $localeEn,
            'language_id' => $languageEn,
            'name' => 'English',
            'territory' => 'United Kingdom',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('locale_translation', [
            'locale_id' => $localeEn,
            'language_id' => $languageZh,
            'name' => '英文',
            'territory' => '英国',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('locale_translation', [
            'locale_id' => $localeZh,
            'language_id' => $languageEn,
            'name' => 'Chinese',
            'territory' => 'China',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('locale_translation', [
            'locale_id' => $localeZh,
            'language_id' => $languageZh,
            'name' => '中文',
            'territory' => '中国',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getEnGbLanguageId(): string
    {
        if (!$this->enGbLanguageId) {
            $this->enGbLanguageId = Uuid::randomHex();
        }

        return $this->enGbLanguageId;
    }
}
