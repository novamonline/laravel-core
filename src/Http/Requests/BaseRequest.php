<?php


namespace Core\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BaseRequest extends FormRequest
{

    public function isMethod($method)
    {
        return Str::lower($method) == Str::lower($this->method());
    }

    public function isCreating()
    {
        return $this->isMethod('POST');
    }

    public function isUpdating()
    {
        return $this->isMethod('PUT') || $this->isMethod('PATCH');
    }

    public function isDeleting()
    {
        return $this->isMethod('DELETE');
    }
}
