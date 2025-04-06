<?php

namespace Freemius\Laravel;

use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class Checkout implements Responsable
{
    private bool $embed = false;

    private bool $media = true;

    private bool $logo = true;

    private bool $desc = true;

    private bool $discount = true;

    private array $checkoutData = [];

    private array $custom = [];

    private ?string $redirectUrl;

    private ?int $customPrice = null;

    public function __construct(private string $store, private string $variant) {}

    public static function make(string $store, string $variant): static
    {
        return new static($store, $variant);
    }

    public function embed(): self
    {
        $this->embed = true;

        return $this;
    }

    public function withoutLogo(): self
    {
        $this->logo = false;

        return $this;
    }

    public function withoutMedia(): self
    {
        $this->media = false;

        return $this;
    }

   
    

    public function withName(string $name): self
    {
        $this->checkoutData['name'] = $name;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->checkoutData['email'] = $email;

        return $this;
    }




    public function redirectTo(string $url): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    

    public function url(): string
    {
    
        //https://checkout.freemius.com/mode/page/product/{product_id}/plan/{plan_id}/?user_email={email}&readonly_user=true
        return $response['data']['attributes']['url'];
    }

    public function redirect(): RedirectResponse
    {
        return Redirect::to($this->url(), 303);
    }

    public function toResponse($request): RedirectResponse
    {
        return $this->redirect();
    }
}
