<?php
namespace Verse;

interface Paginable {
    public function getItems();
    public function getPage();
    public function getTotalPages();
}
