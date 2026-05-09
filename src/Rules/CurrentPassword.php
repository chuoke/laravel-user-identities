<?php

namespace Chuoke\UserIdentities\Rules;

use Chuoke\UserIdentities\Models\UserIdentity;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Validator;

class CurrentPassword implements DataAwareRule, ValidationRule, ValidatorAwareRule
{
    protected $user;

    protected $passwordableTypes;

    protected $validator;

    protected $data;

    public function __construct($user)
    {
        $this->user = $user;
        $this->passwordableTypes = config('user-identities.passwordable_types', []);
    }

    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 如果没有密码类型的身份，直接通过
        if (empty($this->passwordableTypes)) {
            return;
        }

        // 获取用户的所有密码类型身份
        /** @var Collection<UserIdentity> $passwordIdentities */
        $passwordIdentities = $this->user->identities()
            ->whereIn('type', $this->passwordableTypes)
            ->get();

        // 检查是否至少有一个身份密码匹配
        foreach ($passwordIdentities as $identity) {
            /** @var UserIdentity $identity */
            if ($identity->verifyCredentials($value)) {
                return;
            }
        }

        $fail('The :attribute is incorrect.');
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The current password is incorrect.';
    }

    /**
     * Set the current validator on the rule.
     */
    public function setValidator(Validator $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Set the data under validation.
     */
    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
