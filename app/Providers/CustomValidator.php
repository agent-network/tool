<?php

namespace App\Providers;

class CustomValidator extends \Illuminate\Validation\Validator
{
    /**
     * カタカナチェック
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateKatakana( $attribute, $value, $parameters )
    {
        return preg_match( '/^[ァ-ヾ ]+$/u', $value );
    }


    /**
     * 半角英数チェック
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateAlnum( $attribute, $value, $parameters )
    {
        return preg_match( '/^[\_\+\-\!\$\%\&\*a-zA-Z0-9]+$/u', $value );
    }


    /**
     * 電話番号チェック
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateTel( $attribute, $value, $parameters )
    {
        $data = $this->data['tel01'] . '-' . $this->data['tel02'] . '-' . $this->data['tel03'];
        return preg_match( '/^[0-9]{2,4}-[0-9]{2,5}-[0-9]{3,5}$/', $data );
    }


    /**
     * FAX番号チェック
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateFax( $attribute, $value, $parameters )
    {
        $data = $this->data['fax01'] . '-' . $this->data['fax02'] . '-' . $this->data['fax03'];
        return preg_match( '/^[0-9]{2,4}-[0-9]{2,5}-[0-9]{3,5}$/', $data );
    }


    /**
     * ケータイ電話番号チェック
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateMobile( $attribute, $value, $parameters )
    {
        $data = $this->data['mobile01'] . '-' . $this->data['mobile02'] . '-' . $this->data['mobile03'];
        return preg_match( '/^[0-9]{2,4}-[0-9]{2,5}-[0-9]{3,5}$/', $data );
    }


    /**
     * 日付適正チェック(誕生日)
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateValiddate( $attribute, $value, $parameters )
    {
        if ( array_key_exists( 'birth_year', $this->data ) )
        {
            if ( !empty( $this->data['birth_year'] ) && !empty( $this->data['birth_month'] ) && !empty( $this->data['birth_day'] ) )
            {
                return checkdate( $this->data['birth_month'], $this->data['birth_day'], $this->data['birth_year'] );
            }
        }
        elseif ( array_key_exists( 'start_year', $this->data ) )
        {
            if ( !empty( $this->data['start_year'] ) && !empty( $this->data['start_month'] ) && !empty( $this->data['start_day'] ) )
            {
                return checkdate( $this->data['start_month'], $this->data['start_day'], $this->data['start_year'] );
            }
        }
        elseif ( array_key_exists( 'end_year', $this->data ) )
        {
            if ( !empty( $this->data['end_year'] ) && !empty( $this->data['end_month'] ) && !empty( $this->data['end_day'] ) )
            {
                return checkdate( $this->data['end_month'], $this->data['end_day'], $this->data['end_year'] );
            }
        }
        return FALSE;
    }


    /**
     * 日付適正チェック（予約日）
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateValidreservedate( $attribute, $value, $parameters )
    {
        if ( array_key_exists( 'reserve_year', $this->data ) )
        {
            if ( !empty( $this->data['reserve_year'] ) && !empty( $this->data['reserve_month'] ) && !empty( $this->data['reserve_day'] ) )
            {
                return checkdate( $this->data['reserve_month'], $this->data['reserve_day'], $this->data['reserve_year'] );
            }
        }
        elseif ( array_key_exists( 'start_year', $this->data ) )
        {
            if ( !empty( $this->data['start_year'] ) && !empty( $this->data['start_month'] ) && !empty( $this->data['start_day'] ) )
            {
                return checkdate( $this->data['start_month'], $this->data['start_day'], $this->data['start_year'] );
            }
        }
        elseif ( array_key_exists( 'end_year', $this->data ) )
        {
            if ( !empty( $this->data['end_year'] ) && !empty( $this->data['end_month'] ) && !empty( $this->data['end_day'] ) )
            {
                return checkdate( $this->data['end_month'], $this->data['end_day'], $this->data['end_year'] );
            }
        }
        return FALSE;
    }


    /**
     * 日付適正チェック(指定日より後ろの日付かどうか)
     *
     * @param string $attribute キー
     * @param string $value 値
     * @param array $parameters
     * @return bool
     */
    public function validateAfterdate( $attribute, $value, $parameters )
    {
        if ( array_key_exists( 'start_year', $this->data ) && array_key_exists( 'end_year', $this->data ) )
        {
            $start = $this->data['start_year'] . sprintf( '%02d', $this->data['start_month'] )
                   . sprintf( '%02d', $this->data['start_day'] );
            $end = $this->data['end_year'] . sprintf( '%02d', $this->data['end_month'] )
                 . sprintf( '%02d', $this->data['end_day'] );
            if ( !isset( $this->data['retain_flg'] ) )
            {
                return $start <= $end ? TRUE : FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        return FALSE;
    }
}
