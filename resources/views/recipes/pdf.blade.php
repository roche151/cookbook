<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $recipe->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            padding: 30px;
        }
        
        h1 {
            font-size: 24pt;
            margin-bottom: 10px;
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        
        .metadata {
            margin: 15px 0 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        
        .metadata-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 10pt;
            color: #666;
        }
        
        .metadata-item strong {
            color: #333;
        }
        
        .tags {
            margin: 10px 0;
        }
        
        .tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 4px 10px;
            margin-right: 5px;
            border-radius: 3px;
            font-size: 9pt;
            color: #495057;
        }
        
        .description {
            font-size: 11pt;
            color: #555;
            margin: 15px 0 20px 0;
            font-style: italic;
            line-height: 1.8;
        }
        
        .section {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .section-title i {
            color: #3498db;
            margin-right: 8px;
        }
        
        ul, ol {
            margin-left: 20px;
        }
        
        ul li, ol li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        ul {
            list-style-type: none;
        }
        
        ul li:before {
            content: "✓ ";
            color: #27ae60;
            font-weight: bold;
            margin-right: 8px;
        }
        
        ol li {
            padding-left: 5px;
        }
        
        .ingredient-amount {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #999;
            text-align: center;
        }
        
        .rating {
            color: #f39c12;
            font-size: 10pt;
        }
        
        .difficulty-easy { color: #27ae60; }
        .difficulty-medium { color: #f39c12; }
        .difficulty-hard { color: #e74c3c; }
        
        .recipe-image {
            margin: 20px 0;
            text-align: center;
        }
        
        .recipe-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <h1>{{ $recipe->title }}</h1>
    
    <div class="metadata">
        @php
            $displayTime = '';
            if (is_numeric($recipe->time) && (int)$recipe->time > 0) {
                $total = (int)$recipe->time;
                $h = intdiv($total, 60);
                $m = $total % 60;
                $parts = [];
                if ($h > 0) $parts[] = $h . 'h';
                if ($m > 0) $parts[] = $m . 'm';
                $displayTime = $parts ? implode(' ', $parts) : '';
            }
        @endphp
        
        @if($displayTime)
            <span class="metadata-item">
                <strong>Time:</strong> {{ $displayTime }}
            </span>
        @endif
        
        @if($recipe->difficulty)
            <span class="metadata-item">
                <strong>Difficulty:</strong> 
                <span class="difficulty-{{ $recipe->difficulty }}">
                    {{ ucfirst($recipe->difficulty) }}
                </span>
            </span>
        @endif
        
        @php
            $avgRating = $recipe->averageRating();
            $ratingsCount = $recipe->ratingsCount();
        @endphp
        @if($avgRating)
            <span class="metadata-item">
                <strong>Rating:</strong> 
                <span class="rating">
                    {{ number_format($avgRating, 1) }}/5 ({{ $ratingsCount }} {{ Str::plural('review', $ratingsCount) }})
                </span>
            </span>
        @endif
    </div>
    
    @if($recipe->tags && $recipe->tags->count())
        <div class="tags">
            @foreach($recipe->tags as $tag)
                <span class="tag">{{ $tag->name }}</span>
            @endforeach
        </div>
    @endif
    
    @if($recipe->description)
        <div class="description">
            {{ $recipe->description }}
        </div>
    @endif
    
    @if(isset($pdfImagePath) && $pdfImagePath)
        <div class="recipe-image">
            <img src="{{ $pdfImagePath }}" alt="{{ $recipe->title }}">
        </div>
    @endif
    
    @if($recipe->ingredients && $recipe->ingredients->count())
        <div class="section">
            <h2 class="section-title">Ingredients</h2>
            <ul>
                @foreach($recipe->ingredients as $ingredient)
                    <li>
                        @if($ingredient->amount)
                            <span class="ingredient-amount">{{ $ingredient->amount }}</span>
                        @endif
                        {{ $ingredient->name }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if($recipe->directions && $recipe->directions->count())
        <div class="section">
            <h2 class="section-title">Method</h2>
            <ol>
                @foreach($recipe->directions as $direction)
                    <li>{{ $direction->body }}</li>
                @endforeach
            </ol>
        </div>
    @endif
    
    <div class="footer">
        Generated from {{ config('app.name') }} • {{ now()->format('F j, Y') }}
        {{-- @if($recipe->user)
            • Recipe by {{ $recipe->user->name }}
        @endif --}}
    </div>
</body>
</html>
