<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Hoặc kiểm tra quyền người dùng
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:10',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-\_\.\,\!\?\(\)]+$/u'
            ],
            'content' => [
                'required',
                'string',
                'min:50',
                'max:50000'
            ],
            'type' => [
                'required',
                'in:post,announcement'
            ],
            'club_id' => [
                'required',
                'exists:clubs,id'
            ],
            'status' => [
                'required',
                'in:published,hidden'
            ],
            'images' => [
                'nullable',
                'array',
                'max:10' // Tối đa 10 ảnh
            ],
            'images.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ],
            'featured_image' => [
                'nullable',
                'integer',
                'min:0'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Title messages
            'title.required' => 'Tiêu đề bài viết là bắt buộc.',
            'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
            'title.regex' => 'Tiêu đề chứa ký tự không hợp lệ.',
            
            // Content messages
            'content.required' => 'Nội dung bài viết là bắt buộc.',
            'content.min' => 'Nội dung phải có ít nhất :min ký tự.',
            'content.max' => 'Nội dung không được vượt quá :max ký tự.',
            
            // Type messages
            'type.required' => 'Vui lòng chọn loại bài viết.',
            'type.in' => 'Loại bài viết không hợp lệ.',
            
            // Club messages
            'club_id.required' => 'Vui lòng chọn câu lạc bộ.',
            'club_id.exists' => 'Câu lạc bộ không tồn tại.',
            
            // Status messages
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            
            // Images messages
            'images.array' => 'Định dạng ảnh không hợp lệ.',
            'images.max' => 'Chỉ được upload tối đa :max ảnh.',
            'images.*.image' => 'File phải là ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif hoặc webp.',
            'images.*.max' => 'Kích thước ảnh không được vượt quá 5MB.',
            'images.*.dimensions' => 'Kích thước ảnh phải từ 100x100px đến 4000x4000px.',
            
            // Featured image messages
            'featured_image.integer' => 'Ảnh đại diện không hợp lệ.',
            'featured_image.min' => 'Ảnh đại diện không hợp lệ.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'tiêu đề',
            'content' => 'nội dung',
            'type' => 'loại bài viết',
            'club_id' => 'câu lạc bộ',
            'status' => 'trạng thái',
            'images' => 'ảnh',
            'featured_image' => 'ảnh đại diện'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from title and content
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title)
            ]);
        }
        
        if ($this->has('content')) {
            $this->merge([
                'content' => trim($this->content)
            ]);
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Có thể thêm logic sau khi validation thành công
    }
}