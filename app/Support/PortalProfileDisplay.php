<?php

namespace App\Support;

use App\Category;
use App\User;
use App\WebCities;

/**
 * Stable profile keys for portal / analyser clients.
 *
 * Analyser login stores a single users.name (e.g. "sandeep singh") and does not
 * always create web_personal_details, so last name / designation / category /
 * city are derived here when nested rows are empty.
 */
class PortalProfileDisplay
{
    /**
     * @param  array<string, mixed>  $user
     * @return array<string, mixed>
     */
    public static function mergeIntoUserArray(array $user, User $model): array
    {
        $fields = self::fieldsFromArray($user, $model->name);

        $personal = is_array($user['get_web_personal_details'] ?? null)
            ? $user['get_web_personal_details']
            : [];

        if (($personal['firstname'] ?? '') === '' && $fields['first_name'] !== null) {
            $personal['firstname'] = $fields['first_name'];
        }
        if (($personal['lastname'] ?? '') === '' && $fields['last_name'] !== null) {
            $personal['lastname'] = $fields['last_name'];
        }
        if (($personal['district'] ?? '') === '' && $fields['business_city'] !== null) {
            $personal['district'] = $fields['business_city'];
        }
        $user['get_web_personal_details'] = $personal;

        $business = is_array($user['get_web_business_details'] ?? null)
            ? $user['get_web_business_details']
            : [];

        if (($business['designation'] ?? '') === '' && $fields['designation'] !== null) {
            $business['designation'] = $fields['designation'];
        }
        if (empty($business['get_category_details']) && $fields['category_id'] !== null) {
            $business['get_category_details'] = [
                'id' => $fields['category_id'],
                'category' => $fields['category'],
            ];
        }
        if (empty($business['city_rel']) && $fields['business_city_id'] !== null) {
            $business['city_rel'] = [
                'id' => $fields['business_city_id'],
                'city_name' => $fields['business_city'],
            ];
        }
        $business['category'] = $fields['category'];
        $business['category_name'] = $fields['category'];
        $business['city_name'] = $fields['business_city'];
        $user['get_web_business_details'] = $business === [] ? ($user['get_web_business_details'] ?? null) : $business;

        return array_merge($user, $fields);
    }

    /**
     * Compact keys for analyser login `data.user`.
     *
     * @return array<string, mixed>
     */
    public static function compactForUser(User $model): array
    {
        $model->loadMissing([
            'getWebPersonalDetails.districtRel:id,city_name,state_id',
            'getWebBusinessDetails.cityRel:id,city_name',
            'getWebBusinessDetails.getCategoryDetails:id,category',
        ]);

        $user = $model->toArray();

        return self::fieldsFromArray($user, $model->name);
    }

    /**
     * @param  array<string, mixed>  $user
     * @return array{
     *     first_name:?string,
     *     last_name:?string,
     *     firstname:?string,
     *     lastname:?string,
     *     designation:?string,
     *     category:?string,
     *     category_name:?string,
     *     category_id:?int,
     *     business_city:?string,
     *     business_city_id:?int,
     *     district:?string
     * }
     */
    public static function fieldsFromArray(array $user, ?string $fullName = null): array
    {
        $personal = is_array($user['get_web_personal_details'] ?? null)
            ? $user['get_web_personal_details']
            : [];
        $business = is_array($user['get_web_business_details'] ?? null)
            ? $user['get_web_business_details']
            : [];

        [$firstName, $lastName] = self::splitName(
            self::nonEmpty($personal['firstname'] ?? null),
            self::nonEmpty($personal['lastname'] ?? null),
            self::nonEmpty($fullName ?? ($user['name'] ?? null))
        );

        $designation = self::nonEmpty($business['designation'] ?? null);

        [$categoryId, $categoryName] = self::resolveCategory($business);
        [$cityId, $cityName] = self::resolveCity($business, $personal);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'designation' => $designation,
            'category' => $categoryName,
            'category_name' => $categoryName,
            'category_id' => $categoryId,
            'business_city' => $cityName,
            'business_city_id' => $cityId,
            'district' => $cityName,
        ];
    }

    /**
     * @return array{0:?string,1:?string}
     */
    public static function splitName(?string $firstName, ?string $lastName, ?string $fullName): array
    {
        $firstName = self::nonEmpty($firstName);
        $lastName = self::nonEmpty($lastName);

        if ($firstName !== null && $lastName !== null) {
            return [$firstName, $lastName];
        }

        $source = $firstName ?? self::nonEmpty($fullName);
        if ($source === null) {
            return [$firstName, $lastName];
        }

        $parts = preg_split('/\s+/', $source, 2) ?: [];
        $derivedFirst = $parts[0] ?? null;
        $derivedLast = $parts[1] ?? null;

        return [
            $firstName ?? self::nonEmpty($derivedFirst),
            $lastName ?? self::nonEmpty($derivedLast),
        ];
    }

    /**
     * @param  array<string, mixed>  $business
     * @return array{0:?int,1:?string}
     */
    private static function resolveCategory(array $business): array
    {
        $rel = $business['get_category_details'] ?? null;
        $id = null;
        $name = null;
        if (is_array($rel)) {
            $id = isset($rel['id']) && (int) $rel['id'] > 0 ? (int) $rel['id'] : null;
            $name = self::nonEmpty($rel['category'] ?? null);
        }

        $raw = $business['selected_category'] ?? null;
        if ($name === null && $raw !== null && $raw !== '') {
            if (is_numeric($raw)) {
                $id = (int) $raw;
                $row = Category::query()->find($id);
                $name = $row ? self::nonEmpty($row->category) : null;
            } else {
                $name = self::nonEmpty((string) $raw);
                $row = Category::query()->where('category', $name)->first(['id', 'category']);
                if ($row) {
                    $id = (int) $row->id;
                    $name = self::nonEmpty($row->category);
                }
            }
        }

        return [$id, $name];
    }

    /**
     * @param  array<string, mixed>  $business
     * @param  array<string, mixed>  $personal
     * @return array{0:?int,1:?string}
     */
    private static function resolveCity(array $business, array $personal): array
    {
        $rel = $business['city_rel'] ?? ($business['cityRel'] ?? null);
        $id = null;
        $name = null;
        if (is_array($rel)) {
            $id = isset($rel['id']) && (int) $rel['id'] > 0 ? (int) $rel['id'] : null;
            $name = self::nonEmpty($rel['city_name'] ?? null);
        }

        $raw = $business['city'] ?? null;
        if ($name === null && $raw !== null && $raw !== '') {
            if (is_numeric($raw)) {
                $id = (int) $raw;
                $row = WebCities::query()->find($id);
                $name = $row ? self::nonEmpty($row->city_name) : null;
            } else {
                $name = self::nonEmpty((string) $raw);
            }
        }

        if ($name === null) {
            $districtRel = $personal['district_rel'] ?? ($personal['districtRel'] ?? null);
            if (is_array($districtRel)) {
                $id = isset($districtRel['id']) && (int) $districtRel['id'] > 0 ? (int) $districtRel['id'] : $id;
                $name = self::nonEmpty($districtRel['city_name'] ?? null);
            }
            if ($name === null) {
                $personalDistrict = $personal['district'] ?? null;
                if (is_numeric($personalDistrict) && (int) $personalDistrict > 0) {
                    $id = (int) $personalDistrict;
                    $row = WebCities::query()->find($id);
                    $name = $row ? self::nonEmpty($row->city_name) : null;
                } else {
                    $name = self::nonEmpty($personalDistrict);
                }
            }
        }

        return [$id, $name];
    }

    private static function nonEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
