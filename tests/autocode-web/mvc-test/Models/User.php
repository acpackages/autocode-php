<?php

class User {
    public string $name;
    public string $email;

    public function fromJson(array $data): void {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
    }
}
