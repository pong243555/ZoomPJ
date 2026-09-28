<?php

return [
    'after' => ':attribute ต้องเป็นวันที่หลังจาก :date',
    'date' => ':attribute ต้องเป็นวันที่ที่ถูกต้อง',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'in' => 'ค่า :attribute ที่เลือกไม่ถูกต้อง',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'max' => [
        'integer' => ':attribute ต้องไม่มากกว่า :max',
        'string' => ':attribute ต้องมีความยาวไม่เกิน :max ตัวอักษร',
    ],
    'min' => [
        'integer' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีความยาวอย่างน้อย :min ตัวอักษร',
    ],
    'required' => 'กรุณากรอก:attribute',
    'string' => ':attribute ต้องเป็นข้อความ',
    'attributes' => [
        'agenda' => 'วาระการประชุม',
        'duration' => 'ระยะเวลา',
        'email' => 'อีเมล',
        'locale' => 'ภาษา',
        'password' => 'รหัสผ่าน',
        'start_time' => 'เวลาเริ่มต้น',
        'topic' => 'หัวข้อการประชุม',
    ],
];
