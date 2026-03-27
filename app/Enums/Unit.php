<?php

namespace App\Enums;

use phpDocumentor\Reflection\Types\Self_;

enum Unit: String
{
    case ML = "ml";
    case CL = "cl";
    case G = "g";
    case PIECE = "piece";
    case HALF_PIECE = "half piece";
    case QUARTER_PIECE = "quarter piece";
    case EIGHT_PIECE = "eighth piece";
    case SLICE = "slice";
    case TSP = "tsp";
    case TBSP = "tbsp";
    case PINCH = "pinch";
    case NO_UNIT = "-";
}

