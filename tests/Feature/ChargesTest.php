<?php

namespace Tests\Feature;

class ChargesTest extends FeatureTestCase
{
    public function test_customers_can_retrieve_a_single_charge_link()
    {
        $customer = $this->createCustomer();

        $url = $customer->charge(0, 'Test Product');

        $this->assertStringContainsString('https://checkout.paddle.com/checkout/custom/', $url);
    }

    public function test_customers_can_retrieve_a_product_charge_link()
    {
        $customer = $this->createCustomer();

        $url = $customer->chargeProduct($_ENV['PADDLE_TEST_PRODUCT']);

        $this->assertStringContainsString('https://checkout.paddle.com/checkout/custom/', $url);
    }
}
