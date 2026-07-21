import { defineConfig } from "vite";
import { fileURLToPath } from "url";
import path from "path";
import preact from "@preact/preset-vite";

export default ({ mode }: { mode: string }) => {
  const isProduction = mode === "production";

  return defineConfig({
    root: "./src",
    base: isProduction ? "/themes/fuwari/assets/dist/" : "",
    plugins: [preact()],
    css: {
      preprocessorOptions: {
        stylus: {
          additionalData: ``,
        },
      },
    },
    define: {
      "process.env": process.env,
    },
    build: {
      manifest: isProduction,
      minify: isProduction,
      rollupOptions: {
        input: path.resolve(__dirname, "src/main.ts"),
        output: {
          entryFileNames: "[name].js",
          chunkFileNames: "[name].js",
          assetFileNames: "[name][extname]",
        },
        preserveEntrySignatures: "allow-extension",
      },
      treeshake: false,
      outDir: fileURLToPath(new URL("./templates/assets/dist", import.meta.url)),
      emptyOutDir: true,
    },
    server: {
      origin: "http://localhost:5173",
    },
  });
};
