<?php
namespace NaijaEmoji\Manager;

use Potato\Manager\PotatoModel;
use PDOException;

class EmojiManagerController extends PotatoModel
{
    protected static $table = "emojis";
}
