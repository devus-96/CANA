import { cn } from '@/lib/utils';
import { VideoFile } from '@/types';
import { Video, X } from 'lucide-react';

export function VideoField({
    video,
    onVideoChange,
    id,
    error,
    className,
    name,
}: {
    video?: VideoFile | undefined;
    onVideoChange: (video: VideoFile | undefined) => void;
    id: string;
    error?: string;
    className?: string;
    name: string;
}) {
    const upload = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e: any) => {
                onVideoChange({
                    file: file,
                    src: e.target.result,
                });
            };
        }
    };

    const hasError = !!error && (Array.isArray(error) ? error.length > 0 : true);
    const generatedId = id ?? `input-${name}`;

    return (
        <div>
            <label
                htmlFor={id}
                className={cn(
                    'relative flex h-64 w-full flex-col items-center justify-center overflow-hidden rounded-xl border border-gray-300 bg-gray-100',
                    className,
                    {
                        'border-red-500': hasError,
                    },
                )}
            >
                {video == undefined ? (
                    <>
                        <Video className="h-12 w-12 text-gray-400" />
                        <span className="mt-4 font-medium">Drop or select a video file</span>
                        <span className="mt-2 text-center text-sm text-gray-600">
                            Drop video here or click to
                            <span className="text-primary-600 ml-1 underline">browse</span> your machine
                        </span>
                    </>
                ) : (
                    <label htmlFor={id} className="h-full w-full hover:bg-gray-200/30">
                        <video controls className="h-full w-full rounded-xl object-cover" src={video.src} />
                        <button
                            onClick={(e) => {
                                e.preventDefault();
                                onVideoChange(undefined);
                            }}
                            className="absolute top-2 right-2 rounded-full bg-gray-900/60 p-0.5 text-white transition-all duration-300 ease-in-out hover:bg-gray-900"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </label>
                )}
            </label>

            {hasError && (
                <div className="mt-1 text-sm text-red-500">
                    {Array.isArray(error) ? (
                        <ul>
                            {error.map((msg, i) => (
                                <li key={`${generatedId}-error-${i}`}>{msg}</li>
                            ))}
                        </ul>
                    ) : (
                        <span>{error}</span>
                    )}
                </div>
            )}
            <input type="file" accept="video/*" id={id} className="hidden" name={id} onInput={upload} />
        </div>
    );
}
