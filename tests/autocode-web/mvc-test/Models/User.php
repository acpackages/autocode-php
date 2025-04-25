<?php

class User {
    public string $name;
    public string $email;

    public function instanceFromJson(array $data): void {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
    }
}
