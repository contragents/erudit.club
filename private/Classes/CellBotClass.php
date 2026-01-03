<?php

class CellBot
{
    public int $j; // Строки
    public int $i; // Столбцы
    public float $K; // Коэффициент полезности
    public string $direction; // sleva, sprava, sverhu, vniz

    public function __construct(int $i, int $j, float $K, string $direction)
    {
        $this->j = $j;
        $this->i = $i;
        $this->K = $K;
        $this->direction = $direction;
    }
}