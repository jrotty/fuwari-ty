import { defineConfig } from "vite";
import { fileURLToPath } from "url";
import path from "path";
import preact from "@preact/preset-vite";

export default ({ mode }: { mode: string }) => {
  const isProduction = mode === "production";

  return defineConfig({
    root: "./src",
    base: isProduction ? "./" : "",
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
      outDir: fileURLToPath(new URL("./assets/dist", import.meta.url)),
      emptyOutDir: true,
    },
    server: {
      port: 5173,
      origin: "http://localhost:5173",
      proxy: {
        // 将 PHP 请求代理到后端服务器
        "^(?!/src/).+": {
          target: "http://localhost:8080",
          changeOrigin: true,
        },
      },
    },
  });
};
