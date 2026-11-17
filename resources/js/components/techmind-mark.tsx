import { cn } from '@/lib/utils';

export function TechMindMark({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 28 28"
            fill="none"
            aria-hidden="true"
            className={cn('size-7', className)}
        >
            <rect
                x="1"
                y="1"
                width="26"
                height="26"
                rx="8"
                fill="currentColor"
            />
            <path
                d="M8.5 9.75h11M14 9.75v8.5"
                stroke="white"
                strokeWidth="2"
                strokeLinecap="round"
            />
            <circle cx="20" cy="19" r="3" fill="white" fillOpacity="0.92" />
            <circle cx="20" cy="19" r="1.1" fill="currentColor" />
        </svg>
    );
}
