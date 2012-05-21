<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */

namespace Tivoka;

/**
 * The public interface to all tivoka functions
 * @package Tivoka
 */
abstract class Tivoka
{
	const SPEC_1_0 = 8;             // 000 001 000
	const SPEC_2_0 = 16;            // 000 010 000
	
	/**
	 * Evaluates and returns the passed JSON-RPC spec version
	 * @private
	 * @param string $version spec version as a string (using semver notation)
	 */
	static function validateSpecVersion($version)
	{
		switch($version) {
			case '1.0':
				return Tivoka::SPEC_1_0;
				break;
			case '2.0':
				return Tivoka::SPEC_2_0;
			default:
				throw new Exception\SpecException('Unsupported spec version: '+$version);
		}
	}
}
?>