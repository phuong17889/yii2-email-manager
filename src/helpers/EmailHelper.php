<?php
/**
 * Created by phuong17889.
 * @project visionlink-io
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    9/13/2018
 * @time    9:10 AM
 */

namespace phuong17889\email\helpers;
class EmailHelper {

	/**
	 * @param $email
	 *
	 * @return string
	 */
	public static function protectOff($email) {
		return '<!--email_off-->' . $email . '<!--/email_off-->';
	}
}
