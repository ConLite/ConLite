<?php
/**
 * Abstract implementation of iConUser interface.
 *
 * This class is a basic implementation of iConUser interface. It
 * should be used as base class for specific user class.
 *
 * @package Contenido Backend Classes
 * @subpackages Backend User
 *
 * @version 1.0.0
 * @author Holger Librenz
 * @copyright four for business AG
 */

// include interface...
use ConLite\Exceptions\ConUserException;

cInclude('interfaces', 'interface.conuser.php');

/**
 * This abstract class implements interface iConUser and should
 * be user as base class for backend user classes.
 *
 * @package Contenido Backend Classes
 * @subpackage Backend User
 *
 * @version 0.0.1
 * @author Holger Librenz
 * @copyright four for business AG
 */
abstract class ConUser_Abstract implements iConUser {

	/**
	 * Reference database abstraction instance
	 */
	protected DB_ConLite $db;

	/**
	 * ConLite configuration array
	 *
	 */
	protected array $cfg;

	/**
	 * current User ID
	 */
	private string $userId;

	/**
	 * Login name of current user.
	 */
	private string $userName;

	/**
	 * Holds the password which should be set.
	 */
	private string $password;

	/**
	 * Constructor
	 *
	 * Checks given values and initializes class.
	 *
	 * @throws ConUserException
	 */
	function __construct($aCfg, $oDb = null, $sUserId = null) {
        if (!is_array($aCfg) || count($aCfg) <= 0) {
        	throw new ConUserException ("Illegal configuration array \$aCfg.");
        } else {
        	$this->cfg = $aCfg;
        }

        if (is_null($oDb)) {
            $this->db = new DB_ConLite();
        } elseif ($oDb instanceof DB_ConLite) {
            // is it a contenido DB instance?
            $this->db = $oDb;
        } else {
       		throw new ConUserException("Given value for \$oDb is not a valid DB_ConLite instance!");
       	}

        if (!is_null($sUserId)) {
        	$bLoaded = $this->load($sUserId);

        	if ($bLoaded == true) {
        		$this->userId = $sUserId;
        	} else {
        		throw new ConUserException("No user with given user ID found!");
        	}
        }
	}

//	/**
//	 * This method checks "the mask" of password $sNewPassword. If
//	 * it matches the administrators rules iConUser::PASS_OK will be
//	 * returned.
//	 *
//	 * In this abstract class, it always returns PASS_OK!
//	 *
//	 * @param string $sNewPassword
//	 * @return int
//	 *
//	 * @see iConUser::checkPasswordMask()
//	 */
//	public static function checkPasswordMask($sNewPassword) {
//		return iConUser::PASS_OK;
//	}

//	/**
//	 * Returns true if password $sNewPassword is strong enough.
//	 *
//	 * In this abstract class, it always returns true.
//	 *
//	 * @param string $sNewPassword
//	 * @return int
//	 *
//	 * @see iConUser::checkPasswordStrength()
//	 */
//	public static function checkPasswordStrength($sNewPassword) {
//        return iConUser::PASS_OK;
//	}

	/**
	 * Returns user id, currently set.
	 *
	 * @return string
	 */
	public function getUserId (): string
    {
		return $this->userId;
	}

	/**
	 * Sets user ID.
	 *
	 * @param string $userId
	 */
	public function setUserId (string $userId): void
    {
		$this->userId = $userId;
	}

	/**
	 * Generates new user id based on current username.
	 *
	 * @return string
	 */
	public function generateUserId (): string
    {
		$sResult = "";

		$sCurUserName = $this->getUserName();

		if (!empty($sCurUserName)) {
			$sResult = md5($sCurUserName);
		} else {
			throw new ConUserException("No username set");
		}

		$this->userId = $sResult;

		return $sResult;
	}

    /**
     * Returns username, currently set
     *
     * @return string
     */
	public function getUserName (): string
    {
		return $this->userName;
	}

	/**
	 * Sets up new username.
	 *
	 * @param string $userName
	 */
	public function setUserName (string $userName): void
    {
		$this->userName = $userName;
	}

	/**
	 * Checks password which has to be set and return PASS_* values (i.e.
	 * on success PASS_OK).
	 *
	 * @param string $password
	 * @return int
	 */
	public function setPassword (string $password): int
    {
	   $iResult = iConUser::PASS_OK;

	   $iMaskResult = $this->checkPasswordMask($password);
	   if ($iMaskResult != iConUser::PASS_OK) {
	       $iResult = $iMaskResult;
	   } else {
	       $iStrengthResult = $this->checkPasswordStrength($password);

	       if ($iStrengthResult != iConUser::PASS_OK) {
	           $iResult = $iStrengthResult;
	       } else {
	           $this->password = $password;
	       }
	   }

	   return $iResult;
	}

	/**
	 * Returns (unencoded!) password. This method should never be public
	 * available!
	 *
	 * @return string
	 */
	protected function getPassword (): string
    {
	    return $this->password;
	}
}