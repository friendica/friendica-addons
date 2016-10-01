<?php

abstract class wdcal_local
{
    const LOCAL_US = 0;
    const LOCAL_DE = 1;

    /**
     * @static
     *
     * @return array|wdcal_local[]
     */
    public static function getInstanceClasses()
    {
        return array(
            self::LOCAL_US => 'wdcal_local_us',
            self::LOCAL_DE => 'wdcal_local_de',
        );
    }

    /**
     * @static
     *
     * @param int $config
     *
     * @return null|wdcal_local
     */
    public static function getInstance($config = 0)
    {
        $classes = self::getInstanceClasses();
        if (isset($classes[$config])) {
            return new $classes[$config]();
        }

        return null;
    }

    /**
     * @static
     *
     * @param int $uid
     *
     * @return wdcal_local
     */
    public static function getInstanceByUser($uid = 0)
    {
        $dateformat = get_pconfig($uid, 'dav', 'dateformat');
        $format = self::getInstance($dateformat);
        if ($format == null) {
            $format = self::getInstance(self::LOCAL_US);
        }

        return $format;
    }

    /**
     * @static
     * @abstract
     *
     * @return string
     */
    abstract public static function getLanguageCode();

    /**
     * @abstract
     * @static
     *
     * @return string
     */
    abstract public static function getName();

    /**
     * @static
     * @abstract
     *
     * @return int
     */
    abstract public static function getID();

    /**
     * @param string $str
     *
     * @return int
     */
    public function date_local2timestamp($str)
    {
        $x = $this->date_parseLocal($str);

        return mktime($x['hour'], $x['minute'], $x['second'], $x['month'], $x['day'], $x['year']);
    }

    /**
     * @abstract
     *
     * @param string $str
     *
     * @return array
     */
    abstract public function date_parseLocal($str);

    /**
     * @abstract
     *
     * @param int $ts
     *
     * @return string
     */
    abstract public function date_timestamp2local($ts);

    /**
     * @abstract
     *
     * @param int $ts
     *
     * @return string
     */
    abstract public function date_timestamp2localDate($ts);

    /**
     * @abstract
     *
     * @return int
     */
    abstract public function getFirstDayOfWeek();

    /**
     * @abstract
     *
     * @return string
     */
    abstract public function dateformat_js_dm1();
    /**
     * @abstract
     *
     * @return string
     */
    abstract public function dateformat_js_dm2();

    /**
     * @abstract
     *
     * @return string
     */
    abstract public function dateformat_js_dm3();

    /**
     * @abstract
     *
     * @return string
     */
    abstract public function dateformat_datepicker_js();

    /**
     * @abstract
     *
     * @param int $ts
     *
     * @return string
     */
    abstract public function dateformat_datepicker_php($ts = 0);
}

class wdcal_local_us extends wdcal_local
{
    /**
     * @static
     *
     * @return string
     */
    public static function getLanguageCode()
    {
        return 'en';
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return t('U.S. Time Format (mm/dd/YYYY)');
    }

    /**
     * @static
     *
     * @return int
     */
    public static function getID()
    {
        return wdcal_local::LOCAL_US;
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public function date_parseLocal($str)
    {
        return date_parse_from_format('m/d/Y H:i', $str);
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function date_timestamp2local($ts)
    {
        return date('m/d/Y H:i', $ts);
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function date_timestamp2localDate($ts)
    {
        return date('l, F jS Y', $ts);
    }

    /**
     * @return int
     */
    public function getFirstDayOfWeek()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function dateformat_js_dm1()
    {
        return 'W, M/d';
    }

    /**
     * @return string
     */
    public function dateformat_js_dm2()
    {
        return 'd. L';
    }

    /**
     * @return string
     */
    public function dateformat_js_dm3()
    {
        return 'd L yyyy';
    }

    /**
     * @return string
     */
    public function dateformat_datepicker_js()
    {
        return 'mm/dd/yy';
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function dateformat_datepicker_php($ts = 0)
    {
        return date('m/d/Y', $ts);
    }
}

class wdcal_local_de extends wdcal_local
{
    /**
     * @static
     *
     * @return string
     */
    public static function getLanguageCode()
    {
        return 'de';
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return t('German Time Format (dd.mm.YYYY)');
    }

    /**
     * @static
     *
     * @return int
     */
    public static function getID()
    {
        return wdcal_local::LOCAL_DE;
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public function date_parseLocal($str)
    {
        return date_parse_from_format('d.m.Y H:i', $str);
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function date_timestamp2local($ts)
    {
        return date('d.m.Y H:i', $ts);
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function date_timestamp2localDate($ts)
    {
        return date('l, j. F Y', $ts);
    }

    /**
     * @return int
     */
    public function getFirstDayOfWeek()
    {
        return 1;
    }

    /**
     * @return string
     */
    public function dateformat_js_dm1()
    {
        return 'W, d.M';
    }

    /**
     * @return string
     */
    public function dateformat_js_dm2()
    {
        return 'd. L';
    }

    /**
     * @return string
     */
    public function dateformat_js_dm3()
    {
        return 'd L yyyy';
    }

    /**
     * @return string
     */
    public function dateformat_datepicker_js()
    {
        return 'dd.mm.yy';
    }

    /**
     * @param int $ts
     *
     * @return string
     */
    public function dateformat_datepicker_php($ts = 0)
    {
        return date('d.m.Y', $ts);
    }
}
