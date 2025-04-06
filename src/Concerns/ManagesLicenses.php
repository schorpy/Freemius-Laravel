<?php

namespace Freemius\Laravel\Concerns;

use Freemius\Laravel\Exceptions\FreemiusApiError;
use Freemius\Laravel\Exceptions\LicenseKeyNotFound;
use Freemius\Laravel\Exceptions\LicenseKeyNotValidated;
use Freemius\Laravel\Exceptions\MalformedDataError;
use Freemius\Laravel\Freemius;
use Freemius\Laravel\LicenseKey;
use Freemius\Laravel\LicenseKeyInstance;

trait ManagesLicenses
{
    /**
     * Activate a license key.
     *
     * @param string $key The license key
     * @param string $reference External reference to store in Freemius
     *
     * @throws MalformedDataError
     * @throws FreemiusApiError
     * @throws LicenseKeyNotFound
     */
    public function activateLicense(string $key, string $reference): LicenseKeyInstance
    {
        if (!LicenseKey::notDisabled()->withKey($key)->exists()) {
            throw LicenseKeyNotFound::withKey($key);
        }

        $res = Freemius::api('POST', 'licenses/activate', [
            'license_key' => $key,
            'instance_name' => $reference,
        ]);

        return LicenseKeyInstance::fromPayload($res->json());
    }

    /**
     * @throws MalformedDataError
     * @throws LicenseKeyNotValidated
     */
    public function assertValid(string $licenseKey, ?string $instanceId = null): LicenseKey {
        try {
            $res = Freemius::api('POST', 'licenses/validate', [
                'license_key' => $licenseKey,
                'instance_id' => $instanceId,
            ]);
        } catch (FreemiusApiError $e) {
            throw LicenseKeyNotValidated::withErrorMessage($e->getMessage());
        }

        return LicenseKey::fromPayload($res['data']);
    }
}