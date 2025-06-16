<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TipsController extends Controller
{
    public function getDailyTips(Request $request)
    {
        try {
            $babyId = $request->query('baby_id');
            
            if (!$babyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do bebê não fornecido'
                ], 400);
            }

            $baby = Baby::where('user_id', Auth::id())
                ->findOrFail($babyId);

            // Determinar a faixa etária do bebê
            $ageInMonths = $baby->age_in_months;
            $ageRange = $this->getAgeRange($ageInMonths);

            // Buscar duas dicas para a faixa etária
            $tips = Tip::where('age_range', $ageRange)
                ->inRandomOrder()
                ->take(2)
                ->get();

            return response()->json([
                'success' => true,
                'tips' => $tips
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dicas: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getAgeRange($ageInMonths)
    {
        if ($ageInMonths <= 6) {
            return '0-6';
        } elseif ($ageInMonths <= 12) {
            return '7-12';
        } elseif ($ageInMonths <= 24) {
            return '13-24';
        } else {
            return '25+';
        }
    }
} 