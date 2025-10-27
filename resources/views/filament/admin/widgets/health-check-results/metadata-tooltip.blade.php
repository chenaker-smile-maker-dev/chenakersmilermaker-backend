@props(['meta'])

@if(!empty($meta))
    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-3 bg-gray-900 dark:bg-gray-800 text-white text-xs rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-72 pointer-events-none">
        <div class="space-y-2">
            <div class="font-bold text-sm border-b border-gray-700 dark:border-gray-600 pb-2 mb-2 text-gray-100">
                Metadata
            </div>
            @foreach($meta as $key => $value)
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-300 text-right">
                        {{ ucfirst(str_replace('_', ' ', $key)) }}:
                    </span>
                    <span class="text-gray-100 font-mono text-left break-all">
                        {{ is_array($value) ? json_encode($value) : (string) $value }}
                    </span>
                </div>
            @endforeach
        </div>
        <!-- Arrow -->
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
            <div class="border-8 border-transparent border-t-gray-900 dark:border-t-gray-800"></div>
        </div>
    </div>
@endif
