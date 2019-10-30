<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Detail level of an accounting plan. It is related to a single account.
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
class Subcuenta extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Account code.
     *
     * @var string
     */
    public $codcuenta;

    /**
     * Identifier of the special account.
     *
     * @var string
     */
    public $codcuentaesp;

    /**
     * Exercise code.
     *
     * @var string
     */
    public $codejercicio;

    /**
     * Sub-account code.
     *
     * @var string
     */
    public $codsubcuenta;

    /**
     * Amount of the debit.
     *
     * @var float|int
     */
    public $debe;

    /**
     *
     * @var Ejercicio[]
     */
    private static $ejercicios;

    /**
     * Description of the subaccount.
     *
     * @var string
     */
    public $descripcion;

    /**
     *
     * @var bool
     */
    private static $disableAditionTest = false;

    /**
     * Amount of credit.
     *
     * @var float|int
     */
    public $haber;

    /**
     * ID of the account to which it belongs.
     *
     * @var int
     */
    public $idcuenta;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idsubcuenta;

    /**
     * Balance amount.
     *
     * @var float|int
     */
    public $saldo;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->codejercicio = $this->getDefaultCodejercicio();
        $this->debe = 0.0;
        $this->haber = 0.0;
        $this->saldo = 0.0;
    }

    /**
     *
     */
    public function disableAditionalTest()
    {
        self::$disableAditionTest = true;
    }

    /**
     *
     * @return string
     */
    public function getSpecialAccountCode()
    {
        if (empty($this->codcuentaesp)) {
            $account = new Cuenta();
            if ($account->loadFromCode($this->idcuenta)) {
                return $account->codcuentaesp;
            }
        }

        return $this->codcuentaesp;
    }

    /**
     * Load de ID for account
     *
     * @return int
     */
    public function getIdAccount(): int
    {
        $where = [
            new DataBaseWhere('codejercicio', $this->codejercicio),
            new DataBaseWhere('codcuenta', $this->codcuenta),
        ];
        $account = new Cuenta();
        $account->loadFromCode('', $where);

        return $account->idcuenta;
    }

    /**
     * Load de ID for subAccount
     *
     * @return int
     */
    public function getIdSubaccount(): int
    {
        $where = [
            new DataBaseWhere('codejercicio', $this->codejercicio),
            new DataBaseWhere('codsubcuenta', $this->codsubcuenta),
        ];
        $subaccount = new Subcuenta();
        foreach ($subaccount->all($where) as $subc) {
            return $subc->idsubcuenta;
        }

        return 0;
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        /// force the parents tables
        new CuentaEspecial();
        new Cuenta();

        return parent::install();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idsubcuenta';
    }

    /**
     *
     * @return string
     */
    public function primaryDescriptionColumn()
    {
        return 'codsubcuenta';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'subcuentas';
    }

    /**
     * Returns True if there is no erros on properties values.
     *
     * @return bool
     */
    public function test()
    {
        $this->codcuenta = trim($this->codcuenta);
        $this->codsubcuenta = trim($this->codsubcuenta);
        $this->descripcion = $this->toolBox()->utils()->noHtml($this->descripcion);
        if (strlen($this->descripcion) < 1 || strlen($this->descripcion) > 255) {
            $this->toolBox()->i18nLog()->warning(
                'invalid-column-lenght',
                ['%column%' => 'descripcion', '%min%' => '1', '%max%' => '255']
            );
            return false;
        }

        if (!self::$disableAditionTest) {
            if (!$this->testErrorInLengthSubAccount()) {
                $this->toolBox()->i18nLog()->warning('account-length-error');
                return false;
            }

            $this->idcuenta = $this->getIdAccount();
            if (empty($this->idcuenta)) {
                $this->toolBox()->i18nLog()->warning('account-data-error');
                return false;
            }
        }

        return parent::test();
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'ListCuenta?activetab=List')
    {
        return parent::url($type, $list);
    }

    /**
     *
     * @return string
     */
    protected function getDefaultCodejercicio()
    {
        if (empty(self::$ejercicios)) {
            $exerciseModel = new Ejercicio();
            self::$ejercicios = $exerciseModel->all();
        }

        foreach (self::$ejercicios as $eje) {
            return $eje->codejercicio;
        }

        return '';
    }

    /**
     * Insert the model data in the database.
     *
     * @param array $values
     *
     * @return bool
     */
    protected function saveInsert(array $values = [])
    {
        if (parent::saveInsert($values)) {
            $accountBalance = new SubcuentaSaldo();
            $accountBalance->idcuenta = $this->idcuenta;
            $accountBalance->idsubcuenta = $this->idsubcuenta;
            for ($index = 1; $index < 13; $index++) {
                $accountBalance->mes = $index;
                $accountBalance->id = null;
                $accountBalance->save();
            }
            return true;
        }

        return false;
    }

    /**
     * Check if exists error in long of subaccount. Returns FALSE if error.
     *
     * @return bool
     */
    private function testErrorInLengthSubAccount(): bool
    {
        foreach (self::$ejercicios as $eje) {
            if ($eje->codejercicio === $this->codejercicio) {
                return strlen($this->codsubcuenta) == $eje->longsubcuenta;
            }
        }

        return false;
    }
}
