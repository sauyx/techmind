import { cn } from '@/lib/utils';

type SectionHeadingProps = {
    eyebrow: string;
    title: string;
    description?: string;
    align?: 'left' | 'center';
};

export function SectionHeading({
    eyebrow,
    title,
    description,
    align = 'left',
}: SectionHeadingProps) {
    return (
        <div
            className={cn(
                'max-w-2xl',
                align === 'center' && 'mx-auto text-center',
            )}
        >
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-brand">
                {eyebrow}
            </p>
            <h2 className="mt-3 text-balance text-3xl font-semibold tracking-[-0.04em] text-zinc-950 sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
                {title}
            </h2>
            {description ? (
                <p className="mt-4 text-pretty text-lg leading-relaxed text-zinc-600">
                    {description}
                </p>
            ) : null}
        </div>
    );
}
