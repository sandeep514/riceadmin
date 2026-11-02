<?php

namespace Tests\Unit;

use App\Support\PortalProfileDisplay;
use Tests\TestCase;

class PortalProfileDisplayTest extends TestCase
{
    public function test_split_name_uses_last_word_when_login_has_single_name(): void
    {
        [$first, $last] = PortalProfileDisplay::splitName(null, null, 'sandeep singh');

        $this->assertSame('sandeep', $first);
        $this->assertSame('singh', $last);
    }

    public function test_split_name_keeps_explicit_personal_names(): void
    {
        [$first, $last] = PortalProfileDisplay::splitName('Sandeep', 'Singh', 'sandeep singh extra');

        $this->assertSame('Sandeep', $first);
        $this->assertSame('Singh', $last);
    }

    public function test_profile_keys_from_business_and_name(): void
    {
        $fields = PortalProfileDisplay::fieldsFromArray([
            'name' => 'sandeep singh',
            'get_web_personal_details' => null,
            'get_web_business_details' => [
                'designation' => 'Analyst',
                'selected_category' => 9,
                'get_category_details' => ['id' => 9, 'category' => 'Analyser'],
                'city' => 44,
                'city_rel' => ['id' => 44, 'city_name' => 'Karnal'],
            ],
        ], 'sandeep singh');

        $this->assertSame('sandeep', $fields['first_name']);
        $this->assertSame('singh', $fields['last_name']);
        $this->assertSame('singh', $fields['lastname']);
        $this->assertSame('Analyst', $fields['designation']);
        $this->assertSame('Analyser', $fields['category']);
        $this->assertSame(9, $fields['category_id']);
        $this->assertSame('Karnal', $fields['business_city']);
        $this->assertSame(44, $fields['business_city_id']);
        $this->assertSame('Karnal', $fields['district']);
    }
}
