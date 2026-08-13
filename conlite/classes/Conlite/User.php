<?php

namespace ConLite\Conlite;

use ConLite\Database\DbConLite;
use ConLite\GenericDb\Item;
use ConLite\System\Date;
use ConLite\System\Security;
use Contenido_Perm;
use Group;

class User extends Item
{
    /**
     * Password is ok and stored.
     *
     * @var int
     * @final
     */
    const PASS_OK = 0;

    /**
     * Given password is too short
     *
     * @var int
     * @final
     */
    const PASS_TO_SHORT = 1;

    /**
     * Given password is not strong enough
     *
     * @var int
     * @final
     */
    const PASS_NOT_STRONG = 2;

    /**
     * Given password is not complex enough
     *
     * @var int
     * @final
     */
    const PASS_NOT_COMPLEX = 3;

    /**
     * Password does not contain enough numbers.
     *
     * @var int
     * @final
     */
    const PASS_NOT_ENOUGH_NUMBERS = 4;

    /**
     * Password does not contain enough symbols.
     *
     * @var int
     * @final
     */
    const PASS_NOT_ENOUGH_SYMBOLS = 5;

    /**
     * Password does not contain enough mixed characters.
     *
     * @var int
     * @final
     */
    const PASS_NOT_ENOUGH_MIXED_CHARS = 6;

    /**
     * Password does not contain enough different characters.
     *
     * @var int
     * @final
     */
    const PASS_NOT_ENOUGH_DIFFERENT_CHARS = 7;

    /**
     * This value will be used if no minimum length
     * for passwords are set via $cfg['password']['min_length']
     *
     */
    const MIN_PASS_LENGTH_DEFAULT = 8;

    public function __construct($id = false)
    {
        parent::__construct(\cRegistry::getConfigValue('tab', 'phplib_auth_user_md5'), "user_id");
        $this->setFilters();
        if ($id !== false) {
            $this->loadByPrimaryKey($id);
        }
    }

    public function save(): bool
    {
        if (!$this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        return parent::store();
    }

    /**
     * Stores the modified user object to the database
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     * @param boolean group Specifies if this function should recursively search in groups
     * @return string The value of the retrieved property
     */
    public function getUserProperty($type, $name, $group = false)
    {
        global $cfg, $perm;

        if (!is_object($perm)) {
            $perm = new Contenido_Perm();
        }

        $result = false;

        if ($group) {
            $groups = $perm->getGroupsForUser($this->values['user_id']);

            if (is_array($groups)) {
                foreach ($groups as $value) {
                    $sql = "SELECT value FROM " . $cfg["tab"]["group_prop"] . "
                            WHERE group_id = '" . $value . "'
                              AND type = '$type'
                              AND name = '$name'";
                    $this->db->query($sql);

                    if ($this->db->nextRecord()) {
                        $result = $this->db->f("value");
                    }
                }
            }
        }

        $sql = "SELECT value FROM " . $cfg["tab"]["user_prop"] . "
                WHERE user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "'
                  AND type = '" . Security::escapeDB($type, $this->db) . "'
                  AND name = '" . Security::escapeDB($name, $this->db) . "'";
        $this->db->query($sql);

        if ($this->db->nextRecord()) {
            $result = $this->db->f("value");
        }

        if ($result !== false) {
            return urldecode($result);
        } else {
            return false;
        }
    }

    /**
     * Stores the modified user object to the database
     *
     * @param string $sType Specifies the type (class, category etc) for the property to retrieve
     * @param bool $bGroup Specifies if this function should recursively search in groups
     * @return  array   The value of the retrieved property
     * */
    public function getUserPropertiesByType($sType, $bGroup = false)
    {
        global $cfg, $perm;

        if (!is_object($perm)) {
            $perm = new Contenido_Perm();
        }

        $aResult = array();

        if ($bGroup == true) {
            $aGroups = $perm->getGroupsForUser($this->values['user_id']);

            if (is_array($aGroups)) {
                foreach ($aGroups as $iID) {
                    $sSQL = "SELECT name, value FROM " . $cfg["tab"]["group_prop"] . "
                             WHERE group_id = '" . Security::escapeDB($iID, $this->db) . "'
                                AND type = '" . Security::escapeDB($sType, $this->db) . "'";
                    $this->db->query($sSQL);

                    while ($this->db->nextRecord()) {
                        $aResult[$this->db->f("name")] = urldecode($this->db->f("value"));
                    }
                }
            }
        }

        $sSQL = "SELECT name, value FROM " . $cfg["tab"]["user_prop"] . "
                 WHERE user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "'
                 AND type = '" . Security::escapeDB($sType, $this->db) . "'";
        $this->db->query($sSQL);

        while ($this->db->nextRecord()) {
            $aResult[$this->db->f("name")] = urldecode($this->db->f("value"));
        }

        return $aResult;
    }

    /**
     * Retrieves all available properties of the user
     *
     * @return array|bool
     */
    public function getUserProperties()
    {
        global $cfg;

        $sql = "SELECT type, name FROM " . $cfg["tab"]["user_prop"] . "
                WHERE user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "'";
        $this->db->query($sql);

        if ($this->db->num_rows() == 0) {
            return false;
        }

        $props = array();
        while ($this->db->nextRecord()) {
            $props[] = array("name" => $this->db->f("name"),
                "type" => $this->db->f("type"));
        }

        return $props;
    }

    /**
     * Stores a property to the database
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     * @param string value Specifies the value to insert
     */
    public function setUserProperty($type, $name, $value)
    {
        global $cfg;

        $value = urlencode($value);

        // Check if such an entry already exists
        if ($this->getUserProperty($type, $name) !== false) {
            $sql = "UPDATE " . $cfg["tab"]["user_prop"] . "
                    SET value = '$value'
                    WHERE user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "'
                      AND type = '" . Security::escapeDB($type, $this->db) . "'
                      AND name = '" . Security::escapeDB($name, $this->db) . "'";
            $this->db->query($sql);
        } else {
            $sql = "INSERT INTO  " . $cfg["tab"]["user_prop"] . "
                    SET value = '" . Security::escapeDB($value, $this->db) . "',
                        user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "',
                          type = '" . Security::escapeDB($type, $this->db) . "',
                          name = '" . Security::escapeDB($name, $this->db) . "',
                        iduserprop = " . $this->db->nextid($cfg["tab"]["user_prop"]);
            $this->db->query($sql);
        }
    }

    /**
     * Deletes a user property from the table
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     */
    public function deleteUserProperty($type, $name)
    {
        global $cfg;

        // Check if such an entry already exists
        $sql = "DELETE FROM  " . $cfg["tab"]["user_prop"] . "
                    WHERE user_id = '" . Security::escapeDB($this->values['user_id'], $this->db) . "' AND
                          type = '" . Security::escapeDB($type, $this->db) . "' AND
                          name = '" . Security::escapeDB($name, $this->db) . "'";
        $this->db->query($sql);
    }

    public static function checkPasswordMask($newPassword): int
    {
        $iResult = self::PASS_OK;
        
        $cfgPw = \cRegistry::getConfigValue('password');

        if (isset($cfgPw['check_password_mask']) && $cfgPw['check_password_mask']) {
            // any min length in config set?
            $iMinLength = self::MIN_PASS_LENGTH_DEFAULT;
            if (isset($cfgPw['min_length'])) {
                $iMinLength = ( int )$cfgPw['min_length'];
            }

            // check length...
            if (strlen($newPassword) < $iMinLength) {
                $iResult = self::PASS_TO_SHORT;
            }

            // check password elements

            // numbers.....
            if ($iResult == self::PASS_OK && isset($cfgPw['numbers_mandatory']) &&
                (int)$cfgPw['numbers_mandatory'] > 0) {

                $aNumbersInPassword = array();
                preg_match_all("/[0-9]/", $newPassword, $aNumbersInPassword);

                if (count($aNumbersInPassword[0]) < (int)$cfgPw['numbers_mandatory']) {
                    $iResult = self::PASS_NOT_ENOUGH_NUMBERS;
                }
            }

            // symbols....
            if ($iResult == self::PASS_OK && isset($cfgPw['symbols_mandatory']) &&
                (int)$cfgPw['symbols_mandatory'] > 0) {

                $aSymbols = array();
                $sSymbolsDefault = "/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/";
                if (!empty($cfgPw['symbols_regex'])) {
                    $sSymbolsDefault = $cfgPw['symbols_regex'];
                }

                preg_match_all($sSymbolsDefault, $newPassword, $aSymbols);

                if (count($aSymbols[0]) < (int)$cfgPw['symbols_mandatory']) {
                    $iResult = self::PASS_NOT_ENOUGH_SYMBOLS;
                }
            }

            // mixed case??
            if ($iResult == self::PASS_OK && isset($cfgPw['mixed_case_mandatory']) &&
                (int)$cfgPw['mixed_case_mandatory'] > 0) {

                $aLowerCaseChars = [];
                $aUpperCaseChars = [];

                preg_match_all("/[a-z]/", $newPassword, $aLowerCaseChars);
                preg_match_all("/[A-Z]/", $newPassword, $aUpperCaseChars);

                if ((count($aLowerCaseChars[0]) < (int)$cfgPw['mixed_case_mandatory']) ||
                    (count($aUpperCaseChars[0]) < (int)$cfgPw['mixed_case_mandatory'])) {
                    $iResult = self::PASS_NOT_ENOUGH_MIXED_CHARS;
                }
            }
        }
        return $iResult;
    }

    /**
     * Returns user id, currently set.
     * Alias for {@see Item::getId()}.
     *
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return ($this->isLoaded())? $this->get('user_id'):null;
    }

    /**
     * Returns the groups a user is in
     * @param string $userid
     * @return  array  Real names of groups
     */
    function getGroupsByUserID(string $userid): array
    {

        $db = new DbConLite();

        $sql = "SELECT
                    a.group_id
                FROM
                    " . \cRegistry::getConfigValue('tab', 'groups') . " AS a,
                    " . \cRegistry::getConfigValue('tab', 'groupmembers') . " AS b
                WHERE
                    (a.group_id  = b.group_id)
                    AND
                    (b.user_id = '" . Security::escapeDB($userid, $db) . "')
                ";

        $db->query($sql);

        $arrGroups = array();

        $oGroup = new Group();

        while ($db->nextRecord()) {
            $oGroup->loadGroupByGroupID($db->f('group_id'));
            $sTemp = $oGroup->getField('groupname');
            $sTemp = substr($sTemp, 4, strlen($sTemp) - 4);

            $sDescription = trim($oGroup->getField('description'));

            if ($sDescription != '') {
                $sTemp .= ' (' . $sDescription . ')';
            }

            $arrGroups[] = $sTemp;
        }
        return $arrGroups;
    }


    /**
     * check and set password
     *
     * @param string $password
     * @return int
     */
    public function setPassword(string $password): int
    {
        $checkPassword = self::checkPasswordMask($password);
        if($checkPassword != self::PASS_OK) {
            return $checkPassword;
        }
        var_dump($checkPassword);
        $encodedPassword = $this->encodePassword($password);

        if ($this->get('password') != $encodedPassword) {
            $this->set('password', $encodedPassword);
            $this->set('using_pw_request', '0');
        }

        return self::PASS_OK;
    }

    /**
     * encode given password
     *
     * @param string $password
     * @return string
     */
    public function encodePassword(string $password): string
    {
        return md5($password);
    }
    /**
     * set new username
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        if ($this->get('username') != $username) {
            $this->set('username', $username);
        }
    }

    /**
     * set new realname
     *
     * @param string $realName
     * @return void
     */
    public function setRealName(string $realName): void
    {
        if ($this->get('realname') != $realName) {
            $this->set('realname', $realName);
        }
    }

    /**
     * sanitize and set email
     *
     * @param string $email
     * @return void
     */
    public function setMail(string $email): void
    {
        // Remove all illegal characters from email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $this->get('email') != $email) {
            $this->set('email', $email);
        }
    }

    /**
     * set new telephone number
     *
     * @param string $telephone
     * @return void
     */
    public function setTelNumber(string $telephone): void
    {
        if ($this->get('telephone') != $telephone) {
            $this->set('telephone', $telephone);
        }
    }

    /**
     * set new street
     *
     * @param string $street
     * @return void
     */
    public function setStreet(string $street): void
    {
        if ($this->get('address_street') != $street) {
            $this->set('address_street', $street);
        }
    }

    /**
     * set new city
     *
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        if ($this->get('address_city') != $city) {
            $this->set('address_city', $city);
        }
    }

    /**
     * set new zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip(string $zip): void
    {
        if ($this->get('address_zip') != $zip) {
            $this->set('address_zip', $zip);
        }
    }

    /**
     * set new country
     *
     * @param string $country
     * @return void
     */
    public function setCountry(string $country): void
    {
        if ($this->get('address_country') != $country) {
            $this->set('address_country', $country);
        }
    }

    /**
     * set new address combined
     *
     * @param string $street
     * @param string $city
     * @param string $zip
     * @param string $country
     * @return void
     */
    public function setAddressData(string $street, string $city, string $zip, string $country): void
    {
        $this->setStreet($street);
        $this->setCity($city);
        $this->setZip($zip);
        $this->setCountry($country);
    }

    /**
     * set flag for wysiwig editor usage
     *
     * @param ?bool $useWysi
     * @return void
     */
    public function setUseWysi(?bool $useWysi): void
    {
        if (!is_null($useWysi) && $this->get('wysi') != (int) $useWysi) {
            $this->set('wysi', (int) $useWysi);
        }
    }


    /**
     * set new date to
     * @param string $validateTo
     * @return void
     */
    public function setValidDateTo(string $validateTo): void
    {
        if (Date::isEmptyDate($this->get('valid_to')) && Date::isEmptyDate(trim($validateTo))) {
            return;
        }

        if ($this->get('valid_to') != $validateTo) {
            $this->set('valid_to', $validateTo);
        }
    }

    /**
     * set new date from
     *
     * @param string $validateFrom
     * @return void
     */
    public function setValidDateFrom(string $validateFrom): void
    {
        if (Date::isEmptyDate($this->get('valid_to')) && Date::isEmptyDate(trim($validateFrom))) {
            return;
        }

        if ($this->get('valid_from') != $validateFrom) {
            $this->set('valid_from', $validateFrom);
        }
    }

    /**
     * set new perms
     *
     * @param array|string $perms
     */
    public function setPerms($perms)
    {
        $perms = implode(',', $perms);
        if ($this->get('perms') != $perms) {
            $this->set('perms', $perms);
        }
    }

    public static function userExists(string $userId): bool
    {
        return (new User())->loadByPrimaryKey($userId);
    }

    public static function usernameExists(string $username): bool
    {
        return (new User())->loadBy('username', $username);
    }


    /**
     * This static method provides a simple way to get error messages depending
     * on error code $iErrorCode, which is returned by checkPassword* methods.
     *
     * @param int $iErrorCode
     * @return string
     */
    public static function getErrorString (int $iErrorCode): string
    {
        $cfgPw = \cRegistry::getConfigValue('passwort');

        switch ($iErrorCode) {
            case self::PASS_NOT_ENOUGH_MIXED_CHARS: {
                $sError = sprintf(i18n("Please use at least %d lower and upper case characters in your password!"),
                    $cfgPw['mixed_case_mandatory']);
                break;
            }
            case self::PASS_NOT_ENOUGH_NUMBERS: {
                $sError = sprintf(i18n("Please use at least %d numbers in your password!"),
                    $cfgPw['numbers_mandatory']);
                break;
            }
            case self::PASS_NOT_ENOUGH_SYMBOLS : {
                $sError = sprintf(i18n("Please use at least %d symbols in your password!"),
                    $cfgPw['symbols_mandatory']);
                break;
            }
            case self::PASS_TO_SHORT: {
                $sError = sprintf(i18n("Password is too short! Please use at least %d signs."),
                    ($cfgPw['min_length'] >  0 ? $cfgPw['min_length'] :
                        self::MIN_PASS_LENGTH_DEFAULT));
                break;
            }
            case self::PASS_NOT_ENOUGH_DIFFERENT_CHARS : {
                $sError = sprintf(i18n("Password does not contain enough different characters."));
                break;
            }
            case self::PASS_NOT_STRONG: {
                $sError = i18n("Please choose a more secure password!");
                break;
            }
            default: {
                $sError = "I do not really know whats happened. But your password does not match the
                            policies! Please consult your administrator. The error code is #" . $iErrorCode;
            }

        }

        return $sError;
    }
}