@php
    $reponse = $get('reponseValue') ?? $get('reponse');
    $isFile = $get('isFile') ?? false;
    $question = $get('question');
@endphp

@if($isFile && $reponse)
    @php
        // Extraire le chemin du fichier depuis l'URL
        $fileUrl = $reponse;
        
        // Extraire le chemin depuis l'URL
        $parsedUrl = parse_url($fileUrl);
        $path = $parsedUrl['path'] ?? '';
        
        // Si le chemin contient /storage/, extraire le chemin relatif (user/email_folder/filename ou uploads/filename)
        $relativePath = null;
        $fileName = null;
        
        if (str_contains($path, '/storage/')) {
            $pathParts = explode('/storage/', $path);
            if (isset($pathParts[1])) {
                $relativePath = $pathParts[1];
                $fileName = basename($relativePath);
            }
        } else {
            // Si pas de /storage/, essayer d'extraire directement
            $fileName = basename($path);
        }
        
        // Si on n'a pas de nom de fichier, utiliser le dernier segment de l'URL
        if (!$fileName) {
            $fileName = basename($path);
        }
        
        // Déterminer si c'est une image
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $isImage = in_array($extension, $imageExtensions);
        
        // URL pour visualiser le fichier (utiliser l'URL originale)
        $viewUrl = $fileUrl;
        
        // URL pour télécharger le fichier via l'API
        // L'API cherche dans plusieurs emplacements, donc on utilise juste le nom du fichier
        // Le fichier est stocké avec un UUID, donc on utilise le nom complet
        $downloadUrl = url('/api/v1/download/file/' . urlencode($fileName));
    @endphp
    
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2">
            @if($isImage)
                <a href="{{ $viewUrl }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span class="text-sm font-medium">Visualiser</span>
                </a>
            @endif
            <a href="{{ $downloadUrl }}" download class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span class="text-sm font-medium">Télécharger</span>
            </a>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ $fileName }}
        </div>
        @if($isImage)
            <div class="mt-2">
                <img src="{{ $viewUrl }}" alt="{{ $fileName }}" class="max-w-xs max-h-48 rounded border border-gray-200 dark:border-gray-700" loading="lazy">
            </div>
        @endif
    </div>
@else
    <div>
        @if(is_bool($reponse))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $reponse ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                {{ $reponse ? 'Oui' : 'Non' }}
            </span>
        @elseif(is_numeric($reponse))
            {{ number_format($reponse, 0, ',', ' ') }}
        @elseif($reponse instanceof \DateTime || $reponse instanceof \Carbon\Carbon)
            {{ $reponse->format('d/m/Y') }}
        @elseif(is_string($reponse) && !empty($reponse))
            {{ $reponse }}
        @else
            <span class="text-gray-400 dark:text-gray-500">-</span>
        @endif
    </div>
@endif

