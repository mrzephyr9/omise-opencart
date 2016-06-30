<?php
require_once('./src/system/library/omise-plugin/helpers/transfer.php');

class OmisePluginHelperTransferTest extends PHPUnit_Framework_TestCase {

	public function testCurrencyNotSpecify() {
		$currency = '';

		$transfer_amount = OmisePluginHelperTransfer::amount($currency, 123);

		$this->assertEquals('123', $transfer_amount);
	}

	public function testAmountHasNoDecimal() {
		$amount = 100;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('10000', $transfer_amount);
	}

	public function testAmountHasOneDecimalPlaceWithZero() {
		$amount = 100.0;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('10000', $transfer_amount);
	}

	public function testAmountHasTwoDecimalPlacesWithZero() {
		$amount = 100.00;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('10000', $transfer_amount);
	}

	public function testAmountHasOneDecimalPlace() {
		$amount = 100.2;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", 100.2);

		$this->assertEquals('10020', $transfer_amount);
	}

	public function testAmountHasTwoDecimalPlaces() {
		$amount = 100.25;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('10025', $transfer_amount);
	}

	public function testAmountHasTwoDecimalPlacesWithZeroEnding() {
		$amount = 100.50;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", 100.50);

		$this->assertEquals('10050', $transfer_amount);
	}

	public function testAmountIsOneBaht() {
		$amount = 1;
		$currency = "THB";

		$transfer_amount = OmisePluginHelperTransfer::amount($currency, $amount);

		$this->assertEquals('100', $transfer_amount);
	}

	public function testAmountIsZero() {
		$amount = 0;

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('0', $transfer_amount);
	}

	public function testAmountIsEmptyString() {
		$amount = '';

		$transfer_amount = OmisePluginHelperTransfer::amount("THB", $amount);

		$this->assertEquals('0', $transfer_amount);
	}
}