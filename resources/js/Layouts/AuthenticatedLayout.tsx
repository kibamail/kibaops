import { Topbar } from "@/Components/Dashboard/Topbar";
import { Text } from "@kibamail/owly/text";
import type { PropsWithChildren } from "react";

export default function Authenticated({ children }: PropsWithChildren) {
    return (
        <div className="w-full h-screen border-l border-r kb-border-tertiary">
            <Topbar />
            <main className="w-full kb-background-secondary flex flex-col h-[calc(100vh-5rem)] overflow-y-hidden">
                <div className="w-full pr-2 flex pl-2 h-full">
                    <div className="w-full rounded-lg border border-b kb-border-tertiary overflow-y-auto h-full flex-grow">
                        <div className="flex flex-grow w-full h-full">{children}</div>
                    </div>
                </div>
            </main>
            <div className="w-screen h-8 px-2 flex items-center justify-between">
                <div className="h-full flex items-center gap-2">
                    <div className="w-2 h-2 rounded-full kb-background-positive" />

                    <Text>System operational</Text>
                </div>

                <div className="flex items-center gap-6">
                    <Text>Docs</Text>
                    <Text>Help</Text>
                    <Text>Github</Text>
                </div>
            </div>
        </div>
    );
}
