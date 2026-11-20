import { performance } from 'node:perf_hooks';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8099';
const CONCURRENCY = Number(process.env.CONCURRENCY || 10);
const TOTAL = Number(process.env.TOTAL || 50);

const CRITERIA = [
  'logic_problem_solving','data_analysis','math_statistics','attention_detail',
  'visual_design','creativity','user_empathy','communication','coding',
  'network_infra','security_awareness','business_process'
];

function randomRatings() {
  const r = {};
  for (const c of CRITERIA) r[c] = 1 + Math.floor(Math.random() * 5);
  return r;
}

function parseCookies(headers) {
  const jar = {};
  const setCookie = headers.getSetCookie ? headers.getSetCookie() : [];
  for (const sc of setCookie) {
    const [pair] = sc.split(';');
    const idx = pair.indexOf('=');
    if (idx > 0) jar[pair.slice(0, idx)] = pair.slice(idx + 1);
  }
  return jar;
}

function cookieHeader(jar) {
  return Object.entries(jar).map(([k,v]) => `${k}=${v}`).join('; ');
}

async function oneFlow() {
  const t0 = performance.now();
  const jar = {};
  // GET /tes
  const res1 = await fetch(`${BASE}/tes`);
  const cookies1 = parseCookies(res1.headers);
  Object.assign(jar, cookies1);
  // POST /tes
  const xsrf = decodeURIComponent(jar['XSRF-TOKEN'] || '');
  const payload = { visitor_name: 'LoadTest', ratings: randomRatings() };
  const res2 = await fetch(`${BASE}/tes`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Inertia': 'true',
      'X-XSRF-TOKEN': xsrf,
      'Cookie': cookieHeader(jar),
    },
    body: JSON.stringify(payload),
    redirect: 'manual',
  });
  const cookies2 = parseCookies(res2.headers);
  Object.assign(jar, cookies2);
  let token = null;
  if (res2.status === 302 || res2.status === 303) {
    const loc = res2.headers.get('location') || '';
    const m = loc.match(/\/hasil\/([^/?]+)/);
    token = m ? m[1] : null;
  }
  const tPost = performance.now() - t0;
  let tGetResult = 0;
  let statusResult = null;
  if (token) {
    const r3 = performance.now();
    const res3 = await fetch(`${BASE}/hasil/${token}`, { headers: { 'Cookie': cookieHeader(jar) } });
    statusResult = res3.status;
    tGetResult = performance.now() - r3;
    await res3.text();
  }
  await res1.text();
  await res2.text();
  return { statusPost: res2.status, statusResult, token, tPost, tGetResult, total: performance.now() - t0 };
}

async function run() {
  const results = [];
  let i = 0;
  async function worker() {
    while (true) {
      const idx = i++;
      if (idx >= TOTAL) break;
      try {
        const r = await oneFlow();
        results.push(r);
      } catch (e) {
        console.error('ERR', String(e));
        results.push({ error: String(e) });
      }
    }
  }
  const workers = Array.from({ length: CONCURRENCY }, () => worker());
  await Promise.all(workers);

  const ok = results.filter(r => !r.error && r.statusResult === 200);
  const stats = (arr) => {
    const s = [...arr].sort((a,b)=>a-b);
    const avg = s.reduce((a,b)=>a+b,0)/s.length;
    const p95 = s[Math.floor(s.length*0.95)-1] ?? s[s.length-1];
    return { avg: Math.round(avg), p95: Math.round(p95), max: Math.round(s[s.length-1]), min: Math.round(s[0]) };
  };
  console.log(JSON.stringify({
    total: results.length,
    success: ok.length,
    errors: results.filter(r=>r.error).length,
    non2xxPost: results.filter(r=>!r.error && (r.statusPost < 200 || r.statusPost >= 300 && r.statusPost !== 302 && r.statusPost !== 303)).length,
    non2xxResult: results.filter(r=>!r.error && r.statusResult !== 200).length,
    postTiming: stats(results.filter(r=>!r.error).map(r=>r.tPost)),
    resultTiming: stats(results.filter(r=>!r.error).map(r=>r.tGetResult)),
    totalTiming: stats(results.filter(r=>!r.error).map(r=>r.total)),
  }, null, 2));
}

run();
