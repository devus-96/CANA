export default function AuthLayout({ children }: { children: React.ReactNode }) {
    return (
        <div className="">
            <div className="relative flex items-center justify-center">
                <div className="bg-primary z-30 h-screen w-full max-w-full p-4 md:max-w-1/2 md:bg-white md:p-8">
                    <div className="flex h-full w-full items-center justify-center rounded-lg bg-white">
                        <div className="w-100 rounded-lg bg-white md:w-112.5">{children}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
